<x-layouts.app :title="'Edit KPI'" :breadcrumb="'Manajemen KPI / Edit'">
    <div class="mb-12">
        <h1 class="text-4xl font-extrabold tracking-tight text-primary leading-tight">Perbarui Standar KPI</h1>
        <p class="text-on-surface/40 font-medium mt-4">Perbarui parameter keberhasilan untuk penyesuaian performa terkini.</p>
    </div>

    <form action="{{ route('kpis.update', $kpi->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('kpis._form', ['kpi' => $kpi])
    </form>
</x-layouts.app>
