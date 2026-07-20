<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Documentation;

use Phenogram\GatewayBindings\Tests\TestCase;

final class DocumentationTest extends TestCase
{
    private const DOCUMENTS = [
        'CONTRIBUTING.md',
        'README.md',
        'README.en.md',
        'README.ru.md',
        'SECURITY.md',
        'docs/en/api.md',
        'docs/en/client.md',
        'docs/ru/api.md',
        'docs/ru/client.md',
    ];

    public function testLocalDocumentationLinksResolve(): void
    {
        foreach (self::DOCUMENTS as $relativePath) {
            $path = $this->projectRoot() . '/' . $relativePath;
            $contents = $this->read($path);
            preg_match_all('/\[[^\]]+]\(([^)]+)\)/', $contents, $matches);

            foreach ($matches[1] as $target) {
                if (
                    str_starts_with($target, '#')
                    || str_contains($target, '://')
                ) {
                    continue;
                }

                $targetPath = explode('#', $target, 2)[0];

                self::assertFileExists(
                    dirname($path) . '/' . $targetPath,
                    sprintf('Broken link in %s: %s', $relativePath, $target),
                );
            }
        }
    }

    public function testDocumentedPhpSnippetsHaveValidSyntax(): void
    {
        foreach (self::DOCUMENTS as $relativePath) {
            $contents = $this->read($this->projectRoot() . '/' . $relativePath);
            preg_match_all('/```php\s*\n(.*?)```/s', $contents, $matches);

            foreach ($matches[1] as $snippet) {
                $tokens = token_get_all("<?php\n" . $snippet, TOKEN_PARSE);
                self::assertNotEmpty($tokens);
            }
        }
    }

    public function testBothLanguagesWarnThatCheckSendAbilityCanCharge(): void
    {
        self::assertStringContainsString(
            '`checkSendAbility` is optional. It is not a free dry run.',
            $this->read($this->projectRoot() . '/README.md'),
        );
        self::assertStringContainsString(
            '`checkSendAbility` — необязательный метод. Это не бесплатная пробная проверка.',
            $this->read($this->projectRoot() . '/README.ru.md'),
        );
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail('Failed to read ' . $path);
        }

        return $contents;
    }
}
