<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\PasswordStrengthPackage\Service;

class PasswordStrengthConfig
{
    private int $minLength = 8;
    private ?int $maxLength = null;
    private bool $requireUppercase = false;
    private bool $requireLowercase = false;
    private bool $requireNumber = false;
    private bool $requireSpecialChar = false;

    /** @var list<string> */
    private array $specialChars = ['!', '@', '#', '$', '%', '^', '&', '*', '?', '-', '_'];

    public function setMinLength(int $length): self
    {
        $this->minLength = $length;
        return $this;
    }

    public function setMaxLength(int $length): self
    {
        $this->maxLength = $length;
        return $this;
    }

    public function requireUppercase(bool $value = true): self
    {
        $this->requireUppercase = $value;
        return $this;
    }

    public function requireLowercase(bool $value = true): self
    {
        $this->requireLowercase = $value;
        return $this;
    }

    public function requireNumber(bool $value = true): self
    {
        $this->requireNumber = $value;
        return $this;
    }

    public function requireSpecialChar(bool $value = true): self
    {
        $this->requireSpecialChar = $value;
        return $this;
    }

    public function setSpecialChars(array $chars): self
    {
        $this->specialChars = $chars;
        return $this;
    }

    public function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < $this->minLength) {
            $errors[] = "Password must be at least {$this->minLength} characters.";
        }

        if ($this->maxLength !== null && strlen($password) > $this->maxLength) {
            $errors[] = "Password must be at most {$this->maxLength} characters.";
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain an uppercase letter.';
        }

        if ($this->requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain a lowercase letter.';
        }

        if ($this->requireNumber && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain a number.';
        }

        if ($this->requireSpecialChar && $this->specialChars !== []) {
            $pattern = '/[' . preg_quote(implode('', $this->specialChars), '/') . ']/';
            if (!preg_match($pattern, $password)) {
                $errors[] = 'Password must contain a special character.';
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'score' => $this->calculateScore($password),
        ];
    }

    private function calculateScore(string $password): int
    {
        if ($password === '') {
            return 0;
        }

        $score = 0;

        if (strlen($password) >= $this->minLength) {
            $score++;
        }
        if (strlen($password) >= $this->minLength + 4) {
            $score++;
        }
        if (preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password)) {
            $score++;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score++;
        }
        if ($this->specialChars !== []) {
            $pattern = '/[' . preg_quote(implode('', $this->specialChars), '/') . ']/';
            if (preg_match($pattern, $password)) {
                $score++;
            }
        }

        return min(4, $score);
    }

    public function toArray(): array
    {
        return [
            'minLength' => $this->minLength,
            'maxLength' => $this->maxLength,
            'requireUppercase' => $this->requireUppercase,
            'requireLowercase' => $this->requireLowercase,
            'requireNumber' => $this->requireNumber,
            'requireSpecialChar' => $this->requireSpecialChar,
            'specialChars' => $this->specialChars,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}