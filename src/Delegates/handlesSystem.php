<?php


namespace MeiliSearch\Delegates;


trait handlesSystem
{
    public function health()
    {
        return $this->health->show();
    }

    // Stats

    public function version()
    {
        return $this->version->show();
    }

    public function sysInfo()
    {
        return $this->sysInfo->show();
    }

    public function prettySysInfo()
    {
        return $this->sysInfo->pretty();
    }

    public function stats()
    {
        return $this->stats->show();
    }

    public function getKeys()
    {
        return $this->keys->show();
    }
}