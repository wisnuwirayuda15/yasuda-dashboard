@php
  // Namespace Import
  use Carbon\Carbon;
  use App\Models\Destination;
  use App\Enums\FleetCategory;
  use App\Enums\InvoiceStatus;

  // Main Costs
  $inv = $invoice;
  $order = $inv->order;
  $lembaga = $order->customer->name;
  $date = $order->trip_date->translatedFormat('d F Y');
  $destinations = $order->regency->name . ' (' . Destination::find($order->destinations)->implode('name', ' + ') . ')';
  $mainCosts = $inv->main_costs;
  $totalQty = array_sum(array_map(fn($cost) => (int) $cost['qty'], $mainCosts)) ?: 0;
  $totalPrices = array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['price'], $mainCosts)) ?: 0;
  $totalCashbacks = array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['cashback'], $mainCosts)) ?: 0;
  $totalNetTransactions = $totalPrices - $totalCashbacks;

  // Shirts
  $program = collect($mainCosts)->firstWhere('slug', 'program')['qty'];
  $anak = collect($mainCosts)->firstWhere('slug', 'ibu-anak-pangku')['qty'];
  $kaosPaket = $program + $anak;
  $kaosDiserahkan = $inv->kaos_diserahkan;
  $qtyKaosAnak = $kaosDiserahkan - $kaosPaket;
  $qtyKaosGuru = $inv->qty_kaos_guru;
  $qtyKaosDewasa = $inv->qty_kaos_dewasa;
  $priceKaosAnak = $inv->price_kaos_anak;
  $priceKaosGuru = $inv->price_kaos_guru;
  $priceKaosDewasa = $inv->price_kaos_dewasa;
  $totalPriceKaosAnak = $qtyKaosAnak * $priceKaosAnak;
  $totalPriceKaosGuru = $qtyKaosGuru * $priceKaosGuru;
  $totalPriceKaosDewasa = $qtyKaosDewasa * $priceKaosDewasa;
  $totalPriceKaos = $totalPriceKaosAnak + $totalPriceKaosGuru + $totalPriceKaosDewasa;

  // Seat Charge
  [$totalSeat, $mediumSeat, $bigSeat, $legrestSeat] = [0, 0, 0, 0];
  [$mediumSet, $bigSet, $legrestSet] = [[], [], []];
  foreach ($order->orderFleets as $orderFleet) {
      $fleet = $orderFleet->fleet;
      $totalSeat = $fleet->seat_set->value + $totalSeat;
      if ($fleet->category->value == FleetCategory::MEDIUM->value) {
          array_push($mediumSet, $fleet->seat_set->value);
          $mediumSeat++;
      } elseif ($fleet->category->value == FleetCategory::BIG->value) {
          array_push($bigSet, $fleet->seat_set->value);
          $bigSeat++;
      } elseif ($fleet->category->value == FleetCategory::LEGREST->value) {
          array_push($legrestSet, $fleet->seat_set->value);
          $legrestSeat++;
      }
  }
  $adjustedSeat = $inv->adjusted_seat;
  $emptySeat = $totalSeat - $totalQty - $adjustedSeat;
  $beliKursi = collect($mainCosts)->firstWhere('slug', 'beli-kursi');
  $priceBeliKursi = $beliKursi['price'] - $beliKursi['cashback'];
  $seatCharge = 0.5 * $emptySeat * $priceBeliKursi;

  // Other Information
  $code = $inv->code;
  $notes = $inv->notes;
  $otherCost = $inv->other_cost;
  $downPayments = $inv->down_payments;
  $totalTransactions = $totalNetTransactions + $seatCharge + $totalPriceKaos + $otherCost;
  $totalDp = array_sum(array_map(fn($dp) => (int) $dp['amount'], $downPayments)) ?: 0;
  $kekurangan = $totalTransactions - $totalDp;
  if ($kekurangan == 0) {
      $status = InvoiceStatus::PAID_OFF->value;
  } elseif ($kekurangan > 0) {
      $status = InvoiceStatus::UNDER_PAYMENT->value;
  } else {
      $status = InvoiceStatus::OVER_PAYMENT->value;
  }
@endphp

@extends('pdf.layout.main', ['title' => "{$code}_{$lembaga}_{$order->trip_date->translatedFormat('d-m-Y')}"])

@section('content')
<div class="container mx-auto bg-white text-black">
  <div class="mx-8 my-6">
    <section id="header">
      <div class="grid grid-cols-2">
        <div>
          <img class="h-auto w-64" src="{{ asset('/img/logo-light.svg') }}" alt="logo">
        </div>
        <div class="text-right">
          <p class="font-semibold text-red-600">Transparancy and Intergrity</p>
          <p class="text-sm">Invoice <span class="font-semibold">#{{ $code }}</span></p>
        </div>
      </div>
    </section>
    <section class="mt-6" id="invoice">
      <div class="text-center text-4xl font-black text-red-600">
        Invoice Detail
      </div>
      <div class="text-center font-normal italic">
        Nota Pembayaran Yasuda Jaya Tour Travel
      </div>
    </section>
    <section class="mt-6" id="order">
      <div class="grid grid-cols-2">
        <table class="table-auto border-separate border-spacing-2 font-medium">
          <tbody>
            <tr>
              <td>Lembaga</td>
              <td> :</td>
              <td class="font-bold">{{ $lembaga }}</td>
            </tr>
            <tr>
              <td>Tanggal</td>
              <td> :</td>
              <td class="font-bold">{{ $date }}</td>
            </tr>
            <tr>
              <td>Tujuan</td>
              <td> :</td>
              <td class="font-bold">{{ $destinations }}</td>
            </tr>
          </tbody>
        </table>
        <div class="mr-6 flex justify-end">
          <table class="table-auto border-separate border-spacing-2 text-right font-medium">
            <thead>
              <tr>
                <th></th>
                <th>Jumlah</th>
                <th>Seat</th>
              </tr>
            </thead>
            <tbody>
              @if ($mediumSeat)
                <tr>
                  <td>Medium</td>
                  <td>{{ $mediumSeat }}</td>
                  <td>{{ implode(', ', $mediumSet) }}</td>
                </tr>
              @endif
              @if ($bigSeat)
                <tr>
                  <td>Big Bus</td>
                  <td>{{ $bigSeat }}</td>
                  <td>{{ implode(', ', $bigSet) }}</td>
                </tr>
              @endif
              @if ($legrestSeat)
                <tr>
                  <td>Legrest</td>
                  <td>{{ $legrestSeat }}</td>
                  <td>{{ implode(', ', $legrestSet) }}</td>
                </tr>
              @endif
              <tr>
                <td></td>
                <td>Total Seat</td>
                <td>{{ $totalSeat }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <section class="mt-8" id="main-cost">
      <div class="text-lg font-bold text-red-600">
        Detail Biaya Utama
      </div>
      <div>
        <table class="mt-2 min-w-full table-auto text-left">
          <thead>
            <tr class="border border-transparent border-b-slate-500 border-t-slate-500 text-center">
              <th>Keterangan</th>
              <th>Jumlah</th>
              <th>Harga (Gross)</th>
              <th>Total Transaksi (Gross)</th>
              <th>Cashback</th>
              <th>Total Cashback</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($mainCosts as $cost)
              @php
                $name = $cost['name'];
                $qty = $cost['qty'] ? $cost['qty'] : '-';
                $price = $cost['price'] ? idr($cost['price']) : '-';
                $totalPrice = $cost['price'] * $cost['qty'] != 0 ? idr($cost['price'] * $cost['qty']) : '-';
                $cashback = $cost['cashback'] ? idr($cost['cashback']) : '-';
                $totalCashback = $cost['cashback'] * $cost['qty'] != 0 ? idr($cost['cashback'] * $cost['qty']) : '-';
              @endphp
              <tr class="text-center">
                <td class="text-start">{{ $name }}</td>
                <td>{{ $qty }}</td>
                <td>{{ $price }}</td>
                <td>{{ $totalPrice }}</td>
                <td>{{ $cashback }}</td>
                <td>{{ $totalCashback }}</td>
              </tr>
            @endforeach
            <tr class="border border-transparent border-b-slate-500 border-t-slate-500 text-center font-bold">
              <td class="text-start">Total</td>
              <td>{{ $totalQty }}</td>
              <td></td>
              <td class="bg-black text-white">{{ idr($totalPrices) }}</td>
              <td></td>
              <td class="bg-red-700 text-white">{{ idr($totalCashbacks) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
    <section class="mt-10" id="payment">
      <div class="grid grid-cols-2">
        <div class="mr-6">
          <div class="mb-3 bg-black text-center text-lg font-bold text-white">
            DETAIL PEMBAYARAN
          </div>
          <div>
            <table class="w-full table-auto">
              <tbody>
                <tr>
                  <td class="font-semibold">Transaksi (Gross)</td>
                  <td></td>
                  <td class="text-end font-bold">{{ idr($totalPrices) }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td>Total Cashback</td>
                  <td></td>
                  <td class="border border-transparent border-b-black text-end">{{ idr($totalCashbacks) }}</td>
                  <td>-</td>
                </tr>
                <tr>
                  <td class="font-semibold">Total Transaksi (Net)</td>
                  <td></td>
                  <td class="text-end font-bold">{{ idr($totalNetTransactions) }}</td>
                  <td>-</td>
                </tr>
                <tr>
                  <td>Charge Kursi Kosong</td>
                  <td></td>
                  <td class="text-end">{{ idr($seatCharge) }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td>Tambah Kaos</td>
                  <td></td>
                  <td class="text-end">{{ idr($totalPriceKaos) }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td>Other Cost</td>
                  <td></td>
                  <td class="border border-transparent border-b-black text-end">{{ idr($otherCost) }}</td>
                  <td>+</td>
                </tr>
                <tr>
                  <td class="font-semibold">Total Tagihan</td>
                  <td></td>
                  <td class="text-end font-bold">{{ idr($totalTransactions) }}</td>
                  <td></td>
                </tr>
                @foreach ($downPayments as $dp)
                  @php
                    $name = $dp['name'];
                    $date = Carbon::parse($dp['date'])->translatedFormat('d F Y');
                    $amount = idr($dp['amount']);
                  @endphp
                  <tr>
                    <td>{{ $name }}</td>
                    <td class="text-center">{{ $date }}</td>
                    <td class="text-end {{ $loop->iteration == count($downPayments) ? 'border border-transparent border-b-black' : null }}">{{ $amount }}</td>
                    <td>{{ $loop->iteration == count($downPayments) ? '-' : null }}</td>
                  </tr>
                @endforeach
                <tr>
                  <td class="font-semibold">Kekurangan</td>
                  <td></td>
                  <td class="text-end font-bold">{{ idr($kekurangan) }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td>Status</td>
                  <td></td>
                  <td class="{{ $kekurangan > 0 ? 'bg-red-500' : 'bg-green-500' }} text-center text-white">
                    {{ $status }}
                  </td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="ml-6">
          <div class="mb-3 bg-black text-center text-lg font-bold text-white">
            TAMBAHAN KAOS
          </div>
          <div>
            <div>
              <table class="w-full table-auto">
                <tbody>
                  <tr>
                    <td>Total Kaos Diserahkan</td>
                    <td></td>
                    <td class="text-end">{{ $kaosDiserahkan }}</td>
                  </tr>
                  <tr>
                    <td>Kaos Tercover Paket</td>
                    <td></td>
                    <td class="text-end">{{ $kaosPaket }}</td>
                  </tr>
                  <tr>
                    <td>Selisih Kaos Anak</td>
                    <td>{{ idr($priceKaosAnak) }}</td>
                    <td class="text-end">{{ idr($totalPriceKaosAnak) }}</td>
                  </tr>
                  <tr>
                    <td>Tambahan Kaos Stel Guru</td>
                    <td>{{ idr($priceKaosGuru) }}</td>
                    <td class="text-end">{{ $qtyKaosGuru }}</td>
                  </tr>
                  <tr>
                    <td>Total Biaya Kaos Stel Guru</td>
                    <td></td>
                    <td class="text-end">{{ idr($totalPriceKaosGuru) }}</td>
                  </tr>
                  <tr>
                    <td>Tambahan Kaos Dewasa</td>
                    <td>{{ idr($priceKaosDewasa) }}</td>
                    <td class="text-end">{{ $qtyKaosDewasa }}</td>
                  </tr>
                  <tr>
                    <td>BIaya Kaos Dewasa</td>
                    <td></td>
                    <td class="text-end">{{ idr($totalPriceKaosDewasa) }}</td>
                  </tr>
                  <tr>
                    <td class="font-semibold">Total Tambahan Kaos</td>
                    <td></td>
                    <td class="text-end font-bold">{{ idr($totalPriceKaos) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="my-3 bg-black text-center text-lg font-bold text-white">
              CHARGE KURSI
            </div>
            <div>
              <table class="w-full table-auto">
                <tbody>
                  <tr>
                    <td>Kapasitas Kursi</td>
                    <td></td>
                    <td class="text-end">{{ $totalSeat }}</td>
                  </tr>
                  <tr>
                    <td>Aktual - Kursi Terisi</td>
                    <td></td>
                    <td class="text-end">{{ $totalQty }}</td>
                  </tr>
                  <tr>
                    <td>Adjusted Seat</td>
                    <td></td>
                    <td class="text-end">{{ $adjustedSeat }}</td>
                  </tr>
                  <tr>
                    <td>Kursi Kosong</td>
                    <td></td>
                    <td class="text-end">{{ $emptySeat }}</td>
                  </tr>
                  <tr>
                    <td>
                      <p class="font-semibold">Charge Kursi Kosong</p>
                      <p class="text-xs">50% x Kursi kosong x (Beli Kursi - Cashback)</p>
                    </td>
                    <td></td>
                    <td class="text-end font-bold">{{ idr($seatCharge) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
    @if (filled($notes))
      <section class="mt-16" id="notes">
        <div class="mb-2 text-2xl font-bold text-blue-500">
          Special Notes
        </div>
        <div class="prose min-w-full">
          <div class="w-full rounded-md border-2 border-blue-500 bg-slate-100 px-4 leading-6">
            {!! $notes !!}
          </div>
        </div>
      </section>
    @endif
    <section id="footer" class="mt-44">
      <footer class="text-xs grid grid-cols-2 items-center">
        <div>
          <p>Invoice ini sah dan diproses oleh komputer</p>
          <p>Silakan hubungi <a href="" class="text-red-500 font-bold">Yasuda Jaya Tour</a> apabila anda membutuhkan bantuan.</p>
        </div>
        <div class="text-end">
          <p class="italic">Terakhir diupdate: {{ $inv->updated_at }}</p>
        </div>
      </footer>
    </section>
  </div>
</div>
@endsection
