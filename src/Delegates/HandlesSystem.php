<?php

namespace MeiliSearch\Delegates;

trait HandlesSystem
{
    public function health(): ?array
    {
        return $this->health->show();
    }

    public function version(): array
    {
        return $this->version->show();
    }

    public function stats(): array
    {
        return $this->stats->show();
    }

    public function getKeys(): array
    {
        return $this->keys->show();
    }
}
