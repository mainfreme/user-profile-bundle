<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Http\Request;

use JsonException;
use SWH\UserProfile\Infrastructure\Http\Exception\InvalidJsonBodyException;
use Symfony\Component\HttpFoundation\Request;

final readonly class JsonBody
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        private array $data,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $content = $request->getContent();

        if ('' === $content) {
            throw InvalidJsonBodyException::required();
        }

        try {
            $decoded = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw InvalidJsonBodyException::invalid();
        }

        if (!\is_array($decoded)) {
            throw InvalidJsonBodyException::notObject();
        }

        if ([] !== $decoded && array_is_list($decoded)) {
            throw InvalidJsonBodyException::notObject();
        }

        /** @var array<string, mixed> $decoded */
        return new self($decoded);
    }

    public function string(string $key): string
    {
        $value = $this->data[$key] ?? '';

        return \is_string($value) ? $value : '';
    }

    public function optionalString(string $key): ?string
    {
        if (!\array_key_exists($key, $this->data) || null === $this->data[$key]) {
            return null;
        }

        return \is_string($this->data[$key]) ? $this->data[$key] : null;
    }
}
