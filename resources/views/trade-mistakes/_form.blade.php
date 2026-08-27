<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-6 rounded-2xl border border-white/10 bg-slate-900/70 p-5 sm:p-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <label class="journal-label">Mistake name<input name="name" value="{{ old('name', $tradeMistake->name ?? '') }}" class="journal-input mt-1 @error('name') border-rose-400 @enderror" required></label>
    <label class="journal-label">Description <span class="text-slate-500">(optional)</span><textarea name="description" rows="5" class="journal-input mt-1">{{ old('description', $tradeMistake->description ?? '') }}</textarea></label>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-3 text-sm text-slate-300"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tradeMistake->is_active ?? true)) class="size-4 rounded border-white/20 bg-slate-950 text-amber-400 focus:ring-amber-400">Active and available for new trades</label>
    <div class="flex justify-end gap-3"><a href="{{ isset($tradeMistake) ? route('trade-mistakes.show', $tradeMistake) : route('trade-mistakes.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10">Cancel</a><button class="rounded-lg bg-amber-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">{{ $submitLabel }}</button></div>
</form>
