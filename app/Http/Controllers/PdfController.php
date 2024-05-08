<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;

class PdfController extends Controller
{
  public function invoice(Invoice $invoice)
  {
    return Pdf::view('pdf.invoice-pdf', compact('invoice'))
      ->withBrowsershot(
        function (Browsershot $browsershot) {
          $browsershot
            ->format('A3')
            ->fullPage()
            ->margins(10, 0, 10, 0);
        }
      );
  }
}
