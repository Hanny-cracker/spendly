<?php

namespace App\Livewire\Receipts;

use App\Actions\Transactions\CreateTransaction;
use App\Contracts\AIProvider;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\ReceiptStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Scan extends Component
{
    use WithFileUploads;

    public $image;

    public ?Receipt $receipt = null;

    public string $merchant = '';

    public string $date = '';

    public string $amount = '';

    public string $categoryId = '';

    public string $accountId = '';

    public string $description = '';

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(Auth::check(), 401);
        $this->date = now()->toDateString();
    }

    public function scan(AIProvider $provider): void
    {
        $this->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);
        $user = Auth::user();
        $path = $this->image->store('receipts');
        $this->receipt = Receipt::create(['user_id' => $user->id, 'original_path' => $path, 'original_filename' => $this->image->getClientOriginalName(), 'mime_type' => $this->image->getMimeType(), 'status' => ReceiptStatus::Processing]);
        try {
            $data = $provider->extractReceipt($path, $this->image->getMimeType());
            $this->receipt->update(['merchant' => $data->merchant, 'receipt_date' => $data->date, 'total' => $data->total, 'currency' => $data->currency, 'tax' => $data->tax, 'payment_method' => $data->paymentMethod, 'detected_category' => $data->suggestedCategory, 'raw_extraction' => $data->toArray(), 'status' => ReceiptStatus::Processed, 'processed_at' => now()]);
            $this->merchant = $data->merchant ?? '';
            $this->date = $data->date ?? now()->toDateString();
            $this->amount = (string) ($data->total ?? '');
            $this->categoryId = (string) (Category::query()->where('user_id', $user->id)->whereRaw('LOWER(name) = ?', [strtolower((string) $data->suggestedCategory)])->value('id') ?? '');
        } catch (Throwable $exception) {
            $this->receipt->update(['status' => ReceiptStatus::Failed, 'failure_reason' => 'Extraction failed.']);
            $this->error = 'We could not read this receipt clearly. Try another photo or enter the expense manually.';
        }
    }

    public function confirm(): mixed
    {
        $user = Auth::user();
        $validated = $this->validate(['merchant' => ['required', 'string', 'max:255'], 'date' => ['required', 'date'], 'amount' => ['required', 'numeric', 'gt:0'], 'accountId' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)], 'categoryId' => ['required', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', $user->id)->where('type', 'expense'))], 'description' => ['nullable', 'string']]);
        abort_unless($this->receipt?->user_id === $user->id, 403);
        if ($this->receipt->status === ReceiptStatus::Confirmed || $this->receipt->transaction_id) {
            return $this->redirectRoute('transactions', navigate: true);
        }

        DB::transaction(function () use ($user, $validated): void {
            $transaction = app(CreateTransaction::class)->handle(new CreateTransactionData($user->id, (int) $validated['accountId'], (int) $validated['categoryId'], $validated['merchant'], $validated['description'] ?: null, (float) $validated['amount'], TransactionType::Expense, TransactionStatus::Completed, Carbon::parse($validated['date'])));
            $this->receipt?->update(['transaction_id' => $transaction->id, 'status' => ReceiptStatus::Confirmed, 'confirmed_at' => now()]);
        });

        return $this->redirectRoute('transactions', navigate: true);
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.receipts.scan', ['accounts' => Account::query()->where('user_id', $user->id)->orderByDesc('is_default')->get(), 'categories' => Category::query()->where('user_id', $user->id)->where('type', 'expense')->orderBy('name')->get()])->layout('layouts.app', ['title' => 'Scan Receipt | Spendly', 'header' => 'Transactions']);
    }
}
