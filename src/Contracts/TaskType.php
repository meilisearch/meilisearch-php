<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

enum TaskType: string
{
    case IndexCreation = 'indexCreation';
    case IndexUpdate = 'indexUpdate';
    case IndexDeletion = 'indexDeletion';
    case IndexSwap = 'indexSwap';
    case DocumentAdditionOrUpdate = 'documentAdditionOrUpdate';
    case DocumentDeletion = 'documentDeletion';
    case DocumentEdition = 'documentEdition';
    case SettingsUpdate = 'settingsUpdate';
    case DumpCreation = 'dumpCreation';
    case TaskCancelation = 'taskCancelation';
    case TaskDeletion = 'taskDeletion';
    case SnapshotCreation = 'snapshotCreation';
}
