<?php
// Minimal FPDF 1.86 (stripped header comments). For full license, see http://www.fpdf.org
// This is a lightweight copy suitable for simple receipts.
// Only core features used: AddPage, SetFont, Cell, Ln, Output, SetXY, MultiCell, SetDrawColor, SetFillColor

if (class_exists('FPDF')) return;

class FPDF
{
    protected $page;
    protected $n;
    protected $offsets;
    protected $buffer;
    protected $pages;
    protected $PageInfo = [];
    protected $state;
    protected $compress;
    protected $k;
    protected $DefOrientation;
    protected $CurOrientation;
    protected $StdPageSizes;
    protected $DefPageSize;
    protected $CurPageSize;
    protected $PageSizes;
    protected $wPt;
    protected $hPt;
    protected $w;
    protected $h;
    protected $lMargin;
    protected $tMargin;
    protected $rMargin;
    protected $bMargin;
    protected $cMargin;
    protected $x;
    protected $y;
    protected $lasth;
    protected $LineWidth;
    protected $CoreFonts;
    protected $fonts;
    protected $FontFiles;
    protected $encodings;
    protected $cmaps;
    protected $FontFamily;
    protected $FontStyle;
    protected $underline;
    protected $CurrentFont;
    protected $FontSizePt;
    protected $FontSize;
    protected $DrawColor;
    protected $FillColor;
    protected $TextColor;
    protected $ColorFlag;
    protected $ws;
    protected $AutoPageBreak = true;
    protected $pagestr = '';
    protected $fontObjN = 0;

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->state = 0;
        $this->page = 0;
        $this->n = 1;
        $this->buffer = '';
        $this->pages = [];
        $this->PageSizes = [];
        $this->fonts = [];
        $this->FontFiles = [];
        $this->encodings = [];
        $this->cmaps = [];
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        $this->k = ($unit=='pt') ? 1 : (($unit=='mm') ? 72/25.4 : (($unit=='cm') ? 72/2.54 : 72));
        $this->DefOrientation = $orientation;
        $this->CurOrientation = $orientation;
        $this->StdPageSizes = ['a3'=>[841.89,1190.55],'a4'=>[595.28,841.89],'a5'=>[420.94,595.28],'letter'=>[612,792],'legal'=>[612,1008]];
        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;
        $this->wPt = $size[0];
        $this->hPt = $size[1];
        $this->w = $this->wPt/$this->k;
        $this->h = $this->hPt/$this->k;
        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 10;
        $this->cMargin = 2;
        $this->LineWidth = .2;
        $this->SetAutoPageBreak(true,10);
        $this->CoreFonts = ['courier','helvetica','times','symbol','zapfdingbats'];
        $this->SetFont('helvetica','',12);
    }

    function SetAutoPageBreak($auto, $margin=0){ $this->AutoPageBreak = $auto; $this->bMargin = $margin; }
    function AddPage($orientation='', $size='')
    {
        if ($this->state==0) $this->Open();
        $family = $this->FontFamily; $style = $this->FontStyle; $sizePt = $this->FontSizePt;
        if($orientation=='') $orientation = $this->DefOrientation;
        $this->CurOrientation = $orientation;
        if($size=='') $size = $this->DefPageSize; else $size=$this->_getpagesize($size);
        $this->CurPageSize=$size;
        $this->wPt=$size[0]; $this->hPt=$size[1];
        $this->w=$this->wPt/$this->k; $this->h=$this->hPt/$this->k;
        $this->page++;
        $this->pages[$this->page]='';
        $this->x=$this->lMargin; $this->y=$this->tMargin; $this->FontFamily='';
        $this->state = 2; // now writing page content
        $this->SetFont($family,$style,$sizePt);
    }
    function Open(){ $this->state=1; }
    function SetFont($family, $style='', $size=0){
        // Only set properties; do NOT output text operators here, because
        // _enddoc() wraps page content in streams and writing here would corrupt the file
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        if ($size > 0) $this->FontSizePt = $size;
        $this->FontSize = $this->FontSizePt / $this->k;
    }
    function SetDrawColor($r,$g=null,$b=null){ $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r/255, $g/255, $b/255); }
    function SetFillColor($r,$g=null,$b=null){ $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255); }
    function SetTextColor($r,$g=null,$b=null){ $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255); }
    function SetXY($x, $y){ $this->x=$x; $this->y=$y; }
    function Ln($h=null){ $this->y += ($h===null ? $this->lasth : $h); }
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='L', $fill=false)
    {
        $x=$this->x; $y=$this->y;
        $s='';
        if ($fill || $border==1) {
            $op = $fill ? 'f' : 'S';
            $s.=sprintf('%.2F %.2F %.2F %.2F re %s ', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k, $op);
        }
        if ($txt!=='') {
            // Select font and write text inside a single BT/ET block
            $s.=sprintf('BT /F1 %0.2F Tf %.2F %.2F Td (%s) Tj ET ',
                $this->FontSizePt,
                ($x+1)*$this->k,
                ($this->h-($y+$h-3))*$this->k,
                $this->_escape($txt)
            );
        }
        $this->_out($s);
        $this->lasth=$h; $this->x+=$w;
        if ($ln>0) { $this->x=$this->lMargin; $this->y+=$h; }
    }
    function MultiCell($w, $h, $txt)
    {
        $lines = explode("\n", wordwrap($txt, max(1, intval($w*0.5)), "\n", true));
        foreach($lines as $l){ $this->Cell($w,$h,$l,0,1); }
    }
    function Output($dest='', $name='doc.pdf')
    {
        // Minimal one-font doc
        $this->_enddoc();
        $out = $this->buffer;
        if($dest=='S') return $out;
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen($out));
        header('Content-Disposition: inline; filename="'.$name.'"');
        echo $out;
    }

    // Internals (very simplified)
    function _getpagesize($size){
        if (is_string($size)) $size = $this->StdPageSizes[strtolower($size)];
        return $size;
    }
    function _escape($s){ return str_replace(['\\','(',')',"\r"], ['\\\\','\\(','\\)', ''], $s); }
    function _out($s){
        // Write raw PDF commands either to the current page buffer (state=2)
        // or to the global document buffer when not writing a page.
        if ($this->state==2) {
            $this->pages[$this->page] .= $s."\n";
        } else {
            $this->buffer .= $s."\n";
        }
    }
    function _enddoc(){
        // Switch to document-writing state so that _out() targets the global buffer
        $this->state = 1;
        $wPt=$this->wPt; $hPt=$this->hPt;
        $this->buffer="%PDF-1.3\n";
        $this->offsets=[]; $this->pagestr='';

        // Create a core Helvetica font object once
        $this->_newobj();
        $this->_out("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        $this->_out("endobj");
        $this->fontObjN = $this->n - 1;

        foreach($this->pages as $n=>$p){
            $content="q 1 0 0 1 0 0 cm \n".$p."\nQ";
            $this->_newobj();
            $this->_out("<< /Length ".strlen($content)." >>");
            $this->_out("stream");
            $this->_out($content);
            $this->_out("endstream");
            $this->_out("endobj");
            // The content object number is the last created object id (current n - 1)
            $this->PageInfo[$n]=['content'=>($this->n - 1)];
        }
        $kids='';
        $pagesCount = count($this->pages);
        $pagesObjN = $this->n + $pagesCount; // future object number of /Pages
        foreach($this->pages as $n=>$p){
            // Start a new Page dictionary object
            $this->_newobj();
            $this->_out("<< /Type /Page /Parent ".$pagesObjN." 0 R /MediaBox [0 0 $wPt $hPt] /Resources << /ProcSet [/PDF /Text] /Font << /F1 ".$this->fontObjN." 0 R >> >> /Contents ".$this->PageInfo[$n]['content']." 0 R >>");
            $this->_out("endobj");
            // The page object number is the last created object id (current n - 1)
            $this->PageInfo[$n]['n']=($this->n - 1);
            $kids.=($this->n - 1)." 0 R ";
        }
        // /Pages object (now current object number equals $pagesObjN)
        $this->_newobj();
        $this->_out("<< /Type /Pages /Count ".$pagesCount." /Kids [ $kids ] >>");
        $this->_out("endobj");
        // /Catalog object referencing /Pages
        $this->_newobj();
        $this->_out("<< /Type /Catalog /Pages ".$pagesObjN." 0 R >>");
        $this->_out("endobj");
        $xrefpos = strlen($this->buffer);
        $this->_out("xref");
        $this->_out("0 ".($this->n));
        $this->_out("0000000000 65535 f ");
        for($i=1;$i<$this->n;$i++){
            $off = isset($this->offsets[$i]) ? (int)$this->offsets[$i] : 0;
            $this->_out(sprintf('%010d 00000 n ', $off));
        }
        $this->_out("trailer << /Size ".$this->n." /Root ".($this->n-1)." 0 R >>");
        $this->_out("startxref");
        $this->_out($xrefpos);
        $this->_out("%%EOF");
    }
    function _newobj(){ $this->offsets[$this->n]=strlen($this->buffer); $this->_out($this->n.' 0 obj'); $this->n++; }
}
