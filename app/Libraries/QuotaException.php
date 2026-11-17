<?php

namespace App\Libraries;

/** Dilempar saat API key salah atau kuota / limit Gemini habis -> worker berhenti. */
class QuotaException extends \RuntimeException
{
}