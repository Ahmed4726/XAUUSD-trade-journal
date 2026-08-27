<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trade_date' => [
                'required',
                'date',
            ],

            'trade_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'direction' => [
                'required',
                Rule::in([
                    'buy',
                    'sell',
                ]),
            ],

            'timeframe' => [
                'required',
                Rule::in([
                    '15m',
                    '30m',
                    '1h',
                    '4h',
                ]),
            ],

            'trade_reason_id' => [
                'required',
                'exists:trade_reasons,id',
            ],

            'entry_type_id' => [
                'required',
                'exists:entry_types,id',
            ],

            'trade_mistake_id' => [
                'nullable',
                'exists:trade_mistakes,id',
            ],

            'market_condition' => [
                'required',
                Rule::in([
                    'trending',
                    'ranging',
                    'consolidating',
                    'volatile',
                    'choppy',
                    'clean',
                ]),
            ],

            'entry_price' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'stop_loss' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'take_profit' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'lot_size' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'why_entered' => [
                'nullable',
                'string',
            ],

            'expected_scenario' => [
                'nullable',
                'string',
            ],

            'confirmation_seen' => [
                'nullable',
                'string',
            ],

            'plan_vs_reality' => [
                'nullable',
                'string',
            ],

            'execution_rating' => [
                'nullable',
                'integer',
                'between:1,5',
            ],

            'what_went_well' => [
                'nullable',
                'string',
            ],

            'what_went_wrong' => [
                'nullable',
                'string',
            ],

            'lesson_learned' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['direction', 'entry_price', 'stop_loss', 'take_profit'])) {
                return;
            }

            $direction = $this->string('direction')->toString();
            $entryPrice = (float) $this->input('entry_price');
            $stopLoss = (float) $this->input('stop_loss');
            $takeProfit = (float) $this->input('take_profit');

            if ($direction === 'buy' && $stopLoss >= $entryPrice) {
                $validator->errors()->add('stop_loss', 'For a BUY trade, Stop Loss must be below Entry Price.');
            }

            if ($direction === 'buy' && $takeProfit <= $entryPrice) {
                $validator->errors()->add('take_profit', 'For a BUY trade, Take Profit must be above Entry Price.');
            }

            if ($direction === 'sell' && $stopLoss <= $entryPrice) {
                $validator->errors()->add('stop_loss', 'For a SELL trade, Stop Loss must be above Entry Price.');
            }

            if ($direction === 'sell' && $takeProfit >= $entryPrice) {
                $validator->errors()->add('take_profit', 'For a SELL trade, Take Profit must be below Entry Price.');
            }
        }];
    }
}
