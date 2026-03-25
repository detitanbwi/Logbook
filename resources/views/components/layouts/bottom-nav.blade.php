@props(['active' => 'dashboard'])

<nav class="bg-white/80 backdrop-blur-xl fixed bottom-0 w-full z-50 rounded-t-[2.5rem] border-t border-[#002a58]/5 shadow-[0_-4px_40px_rgba(25,28,30,0.06)] flex justify-around items-center px-6 pb-10 pt-4">
    <!-- Logs Tab -->
    <a href="{{ route('dashboard') }}" 
       class="flex flex-col items-center gap-1.5 transition-all duration-300 ease-in-out {{ $active === 'dashboard' ? 'text-[#002a58] scale-110' : 'text-[#74777f] hover:text-[#004080]' }}">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ $active === 'dashboard' ? '1' : '0' }};">event_note</span>
        <span class="font-['Inter'] text-[0.6rem] font-black tracking-widest uppercase">Logs</span>
    </a>

    <!-- Create Tab -->
    <a href="{{ route('logbooks.create') }}" 
       class="flex flex-col items-center gap-1.5 transition-all duration-300 ease-in-out {{ $active === 'create' ? 'text-[#002a58] scale-110' : 'text-[#74777f] hover:text-[#004080]' }}">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ $active === 'create' ? '1' : '0' }};">add_circle</span>
        <span class="font-['Inter'] text-[0.6rem] font-black tracking-widest uppercase">Create</span>
    </a>

    <!-- Reviews Tab -->
    @if(auth()->user()->subordinates()->exists())
    <a href="{{ route('reviews.index') }}" 
       class="flex flex-col items-center gap-1.5 transition-all duration-300 ease-in-out {{ $active === 'reviews' ? 'text-[#002a58] scale-110' : 'text-[#74777f] hover:text-[#004080]' }}">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ $active === 'reviews' ? '1' : '0' }};">rate_review</span>
        <span class="font-['Inter'] text-[0.6rem] font-black tracking-widest uppercase">Reviews</span>
    </a>
    @endif

    <!-- Profile Tab -->
    <a href="{{ route('employees.show', auth()->id()) }}" 
       class="flex flex-col items-center gap-1.5 transition-all duration-300 ease-in-out {{ $active === 'profile' ? 'text-[#002a58] scale-110' : 'text-[#74777f] hover:text-[#004080]' }}">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ $active === 'profile' ? '1' : '0' }};">person</span>
        <span class="font-['Inter'] text-[0.6rem] font-black tracking-widest uppercase">Profile</span>
    </a>
</nav>
