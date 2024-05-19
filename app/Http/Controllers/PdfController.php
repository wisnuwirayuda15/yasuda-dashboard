<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\PdfBuilder;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

class PdfController extends Controller
{
  /**
   * Generates a PDF file of an invoice.
   *
   * @param string $code The code of the invoice to generate.
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the invoice with the given code is not found.
   * @return \Spatie\LaravelPdf\PdfBuilder The generated PDF file.
   */
  public function invoice(Invoice $invoice): PdfBuilder
  {
    return Pdf::view('pdf.invoice-pdf', compact('invoice'))
      ->margins(10, 0, 10, 0)
      ->format(Format::A2)
      ->name("{$invoice->code}_{$invoice->order->customer->name}_{$invoice->order->trip_date->translatedFormat('d-m-Y')}.pdf");
  }
}
