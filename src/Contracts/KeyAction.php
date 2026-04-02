<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

enum KeyAction: string
{
    case Any = '*';
    case Search = 'search';
    case DocumentsAny = 'documents.*';
    case DocumentsAdd = 'documents.add';
    case DocumentsGet = 'documents.get';
    case DocumentsDelete = 'documents.delete';
    case IndexesAny = 'indexes.*';
    case IndexesCreate = 'indexes.create';
    case IndexesGet = 'indexes.get';
    case IndexesUpdate = 'indexes.update';
    case IndexesDelete = 'indexes.delete';
    case IndexesSwap = 'indexes.swap';
    case IndexesCompact = 'indexes.compact';
    case TasksAny = 'tasks.*';
    case TasksCancel = 'tasks.cancel';
    case TasksDelete = 'tasks.delete';
    case TasksGet = 'tasks.get';
    case TasksCompact = 'tasks.compact';
    case SettingsAny = 'settings.*';
    case SettingsGet = 'settings.get';
    case SettingsUpdate = 'settings.update';
    case StatsAny = 'stats.*';
    case StatsGet = 'stats.get';
    case MetricsAny = 'metrics.*';
    case MetricsGet = 'metrics.get';
    case DumpsAny = 'dumps.*';
    case DumpsCreate = 'dumps.create';
    case SnapshotsCreate = 'snapshots.create';
    case Version = 'version';
    case KeysCreate = 'keys.create';
    case KeysGet = 'keys.get';
    case KeysUpdate = 'keys.update';
    case KeysDelete = 'keys.delete';
    case ExperimentalGet = 'experimental.get';
    case ExperimentalUpdate = 'experimental.update';
    case Export = 'export';
    case NetworkGet = 'network.get';
    case NetworkUpdate = 'network.update';
    case ChatCompletions = 'chatCompletions';
    case ChatsAny = 'chats.*';
    case ChatsGet = 'chats.get';
    case ChatsDelete = 'chats.delete';
    case ChatsSettingsAny = 'chatsSettings.*';
    case ChatsSettingsGet = 'chatsSettings.get';
    case ChatsSettingsUpdate = 'chatsSettings.update';
    case GetAny = '*.get';
    case WebhooksAny = 'webhooks.*';
    case WebhooksGet = 'webhooks.get';
    case WebhooksCreate = 'webhooks.create';
    case WebhooksUpdate = 'webhooks.update';
    case WebhooksDelete = 'webhooks.delete';
    case FieldsPost = 'fields.post';
    case DynamicSearchRulesAny = 'dynamicSearchRules.*';
    case DynamicSearchRulesGet = 'dynamicSearchRules.get';
    case DynamicSearchRulesCreate = 'dynamicSearchRules.create';
    case DynamicSearchRulesUpdate = 'dynamicSearchRules.update';
    case DynamicSearchRulesDelete = 'dynamicSearchRules.delete';
}
