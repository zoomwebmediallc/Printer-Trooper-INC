<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cart.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());
$userId = getUserId();

// Ensure minimal schema for saved cards
try { $db->exec("CREATE TABLE IF NOT EXISTS saved_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    brand VARCHAR(32) NOT NULL,
    last4 VARCHAR(4) NOT NULL,
    exp_month TINYINT UNSIGNED NOT NULL,
    exp_year SMALLINT UNSIGNED NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id)
)"); } catch (Exception $e) {}

$success = '';$error = '';

// Handle add/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $brand = trim($_POST['brand'] ?? '');
        $last4 = preg_replace('/[^0-9]/', '', substr($_POST['last4'] ?? '', -4));
        $exp_month = (int)($_POST['exp_month'] ?? 0);
        $exp_year = (int)($_POST['exp_year'] ?? 0);
        if ($brand && $last4 && $exp_month >= 1 && $exp_month <= 12 && $exp_year >= (int)date('Y')) {
            $stmt = $db->prepare('INSERT INTO saved_cards (user_id, brand, last4, exp_month, exp_year) VALUES (:uid,:b,:l4,:m,:y)');
            if ($stmt->execute([':uid'=>$userId, ':b'=>$brand, ':l4'=>$last4, ':m'=>$exp_month, ':y'=>$exp_year])) {
                $success = 'Card saved.';
            } else { $error = 'Failed to save card.'; }
        } else { $error = 'Please enter valid card details.'; }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM saved_cards WHERE id=:id AND user_id=:uid');
        if ($stmt->execute([':id'=>$id, ':uid'=>$userId])) { $success = 'Card removed.'; } else { $error = 'Failed to remove card.'; }
    }

    if ($action === 'make_default') {
        $id = (int)($_POST['id'] ?? 0);
        $db->beginTransaction();
        try {
            $db->prepare('UPDATE saved_cards SET is_default=0 WHERE user_id=:uid')->execute([':uid'=>$userId]);
            $db->prepare('UPDATE saved_cards SET is_default=1 WHERE id=:id AND user_id=:uid')->execute([':id'=>$id, ':uid'=>$userId]);
            $db->commit();
            $success = 'Default card updated.';
        } catch (Exception $e) { $db->rollBack(); $error = 'Failed to set default.'; }
    }
}

// Fetch cards
$stmt = $db->prepare('SELECT * FROM saved_cards WHERE user_id=:uid ORDER BY is_default DESC, created_at DESC');
$stmt->execute([':uid'=>$userId]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Methods</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://js.stripe.com/v3/"></script>
  <style>
    .pm-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
    .card{background:#fff;border:1px solid var(--border-light);border-radius:14px;box-shadow:var(--shadow-sm)}
    .card-body{padding:1rem}
    .card h3{color:#2c3e50;margin-bottom:.75rem}
    .pm-list .row{display:grid;grid-template-columns:1fr auto auto;gap:.5rem;align-items:center;border-bottom:1px solid #f2f2f2;padding:.75rem 0}
    .pm-list .row:last-child{border-bottom:none}
    @media(max-width:768px){.pm-grid{grid-template-columns:1fr}}
    .stripe-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem}
    @media(max-width:768px){.stripe-grid{grid-template-columns:1fr}}
    .pm-badge{font-size:.8rem;color:#fff;background:var(--primary-color);padding:.15rem .4rem;border-radius:6px;margin-left:.35rem}
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>
  <main class="main-content">
    <div class="container">
      <h1 class="page-title">Payment Methods</h1>

      <?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

      <!-- Stripe-managed Cards -->
      <div class="stripe-grid">
        <div class="card">
          <div class="card-body">
            <h3>My Cards (Stripe)</h3>
            <div id="stripeCards" class="pm-list"></div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h3>Add New Card (Stripe)</h3>
            <div id="stripe-card-element" style="padding:12px;border:1px solid #e0e0e0;border-radius:6px;background:#fafafa;"></div>
            <div id="stripe-card-errors" style="color:#e74c3c;margin-top:8px;"></div>
            <div class="form-actions" style="margin-top:10px;"><button class="btn-primary" id="addStripeCardBtn">Save Card</button></div>
            <small style="color:#6c757d;display:block;margin-top:8px;">Your card will be saved securely with Stripe.</small>
          </div>
        </div>
      </div>

      <!-- Legacy locally-stored cards (kept temporarily) -->
      <div class="pm-grid">
        <div class="card">
          <div class="card-body">
            <h3>Saved Cards</h3>
            <div class="pm-list">
              <?php if (empty($cards)): ?>
                <p style="color:#6c757d">No saved cards yet.</p>
              <?php else: foreach($cards as $c): ?>
                <div class="row">
                  <div>
                    <strong><?php echo h(strtoupper($c['brand'])); ?></strong> •••• <?php echo h($c['last4']); ?>
                    <div style="color:#6c757d;font-size:.9rem;">Exp: <?php echo sprintf('%02d/%d', $c['exp_month'], $c['exp_year']); ?><?php if ($c['is_default']): ?> · Default<?php endif; ?></div>
                  </div>
                  <form method="POST" action="payment_methods.php">
                    <input type="hidden" name="action" value="make_default" />
                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>" />
                    <button class="btn-primary" <?php if ($c['is_default']) echo 'disabled'; ?>>Make Default</button>
                  </form>
                  <form method="POST" action="payment_methods.php" onsubmit="return confirm('Remove this card?')">
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>" />
                    <button class="btn-primary" style="background:#e74c3c;color:#fff;">Remove</button>
                  </form>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h3>Add New Card</h3>
            <form method="POST" action="payment_methods.php">
              <input type="hidden" name="action" value="add" />
              <div class="form-group">
                <label>Brand</label>
                <select name="brand">
                  <option value="visa">Visa</option>
                  <option value="mastercard">Mastercard</option>
                  <option value="amex">Amex</option>
                  <option value="rupay">RuPay</option>
                  <option value="discover">Discover</option>
                </select>
              </div>
              <div class="form-group">
                <label>Last 4 digits</label>
                <input type="text" name="last4" maxlength="4" placeholder="1234" />
              </div>
              <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
                <div>
                  <label>Exp Month</label>
                  <input type="number" min="1" max="12" name="exp_month" placeholder="MM" />
                </div>
                <div>
                  <label>Exp Year</label>
                  <input type="number" min="<?php echo (int)date('Y'); ?>" name="exp_year" placeholder="YYYY" />
                </div>
              </div>
              <div class="form-actions"><button class="btn-primary">Save Card</button></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php include 'includes/footer.php'; ?>
  <script>
    (function(){
      const stripe = Stripe('<?php echo htmlspecialchars($stripePublishableKey ?? "pk_test_51PkpMI08O9gYqTfwYHnxfcYBY2raZGkRqgZSy0QyOy9tbTSMuJjBaE10uaGe0W5DLRxCAVW7elsfp15iQeETKmkZ00OdpLrGxn"); ?>');
      const elements = stripe.elements();
      const card = elements.create('card', { hidePostalCode:true });
      const cardMount = document.getElementById('stripe-card-element');
      if (cardMount) card.mount('#stripe-card-element');

      const listEl = document.getElementById('stripeCards');
      async function refreshList(){
        if (!listEl) return;
        listEl.innerHTML = 'Loading...';
        try {
          const res = await fetch('stripe_list_payment_methods.php');
          const data = await res.json();
          if (!res.ok) throw new Error(data.error || 'Failed to load');
          if (!data.payment_methods || data.payment_methods.length === 0) {
            listEl.innerHTML = '<p style="color:#6c757d">No cards saved yet.</p>';
            return;
          }
          listEl.innerHTML = '';
          data.payment_methods.forEach(pm => {
            const row = document.createElement('div');
            row.className = 'row';
            const left = document.createElement('div');
            left.innerHTML = `<strong>${pm.brand.toUpperCase()}</strong> •••• ${pm.last4} <span style="color:#6c757d;font-size:.9rem;">Exp: ${String(pm.exp_month).padStart(2,'0')}/${pm.exp_year}</span>` + (pm.is_default ? ' <span class="pm-badge">Default</span>' : '');
            const makeBtn = document.createElement('button');
            makeBtn.className = 'btn-primary';
            makeBtn.textContent = 'Make Default';
            if (pm.is_default) makeBtn.disabled = true;
            makeBtn.onclick = async () => {
              makeBtn.disabled = true;
              await fetch('stripe_set_default_payment_method.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({ payment_method_id: pm.id })});
              refreshList();
            };
            const delBtn = document.createElement('button');
            delBtn.className = 'btn-primary';
            delBtn.style.background = '#e74c3c';
            delBtn.style.color = '#fff';
            delBtn.textContent = 'Remove';
            delBtn.onclick = async () => {
              if (!confirm('Remove this card?')) return;
              delBtn.disabled = true;
              await fetch('stripe_delete_payment_method.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({ payment_method_id: pm.id })});
              refreshList();
            };
            row.appendChild(left);
            row.appendChild(makeBtn);
            row.appendChild(delBtn);
            listEl.appendChild(row);
          });
        } catch (e) {
          listEl.innerHTML = `<div class="alert alert-error">${e.message}</div>`;
        }
      }

      async function getSetupIntentSecret(){
        const res = await fetch('stripe_create_setup_intent.php', { method:'POST' });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Failed to initialize');
        return data.clientSecret;
      }

      const addBtn = document.getElementById('addStripeCardBtn');
      const cardErrors = document.getElementById('stripe-card-errors');
      addBtn && addBtn.addEventListener('click', async (e)=>{
        e.preventDefault();
        cardErrors.textContent = '';
        addBtn.disabled = true;
        addBtn.textContent = 'Saving...';
        try {
          const secret = await getSetupIntentSecret();
          const { setupIntent, error } = await stripe.confirmCardSetup(secret, { payment_method: { card } });
          if (error) throw new Error(error.message);
          // Saved at Stripe, refresh list
          await refreshList();
          addBtn.textContent = 'Saved!';
          setTimeout(()=>{ addBtn.textContent='Save Card'; addBtn.disabled=false; }, 1000);
        } catch (err) {
          cardErrors.textContent = err.message;
          addBtn.textContent = 'Save Card';
          addBtn.disabled = false;
        }
      });

      refreshList();
    })();
  </script>
  <script src="./assets/js/script.js"></script>
</body>
</html>
