<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class Version
{
    /**
     * @param non-empty-string $commitSha
     * @param non-empty-string $pkgVersion
     */
    public function __construct(
        private readonly string $commitSha,
        private readonly \DateTimeImmutable $commitDate,
        private readonly string $pkgVersion,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getCommitSha(): string
    {
        return $this->commitSha;
    }

    public function getCommitDate(): \DateTimeImmutable
    {
        return $this->commitDate;
    }

    public function getPkgVersion(): string
    {
        return $this->pkgVersion;
    }

    /**
     * @param array{
     *     commitSha: non-empty-string,
     *     commitDate: non-empty-string,
     *     pkgVersion: non-empty-string
     * } $data
     */
    public static function fromArray(array $data): Version
    {
        return new self(
            $data['commitSha'],
            new \DateTimeImmutable($data['commitDate']),
            $data['pkgVersion'],
        );
    }
}
