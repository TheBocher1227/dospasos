<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
class loginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required', // Obligamos a que reCAPTCHA esté presente
        ];
    }

    public function messages()
    {
        return [
            'g-recaptcha-response.required' => 'Debes completar el reCAPTCHA.',
        ];
    }

    public function validateRecaptcha()
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $this->input('g-recaptcha-response'),
        ]);

        return $response->json()['success'] ?? false;
    }
}
