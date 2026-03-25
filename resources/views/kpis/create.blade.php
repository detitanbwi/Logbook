<x-layouts.app :title="'Tambah KPI'" :breadcrumb="'Manajemen KPI / Tambah'">
    <div class="mb-12">
        <h1 class="text-4xl font-extrabold tracking-tight text-primary leading-tight">Buat Standar KPI Baru</h1>
        <p class="text-on-surface/40 font-medium mt-4">Tentukan parameter keberhasilan baru untuk performa personel.</p>
    </div>

    <form action="{{ route('kpis.store') }}" method="POST">
        @csrf
        @include('kpis._form', ['kpi' => null])
    </form>
</x-layouts.app>
