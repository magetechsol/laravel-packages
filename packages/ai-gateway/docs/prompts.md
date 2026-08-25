# Prompt Management

Deep dive into the MTS AI Gateway prompt management system.

## Overview

Prompt management allows you to define, version, and reuse AI prompt templates. Templates are stored in the database and resolved at runtime with variable substitution.

## Creating Prompts

### Via Database

```php
use MageTech\AIGateway\Models\Prompt;

Prompt::create([
    'name' => 'summarize-text',
    'version' => 1,
    'template' => 'Summarize the following text in {{ tone }} tone:\n\n{{ text }}',
    'variables' => ['text', 'tone'],
    'model' => 'gpt-4o',
    'temperature' => 0.5,
    'max_tokens' => 500,
    'status' => 'active',
]);
```

### Via PromptManager

```php
$manager = app(PromptManager::class);

$template = $manager->create([
    'name' => 'summarize-text',
    'template' => 'Summarize: {{ text }}',
    'model' => 'gpt-4o',
]);
```

## Template Syntax

Templates use `{{ variable }}` or `{{variable}}` syntax:

```
Write a {{ tone }} product description for {{ product_name }}.
Key features: {{ features }}
Target audience: {{ audience }}
```

## Versioning

Each prompt can have multiple versions. When retrieving a prompt without specifying a version, the latest active version is returned.

```php
// Create version 1
$manager->create(['name' => 'greeting', 'template' => 'Hello {{ name }}']);

// Create version 2
$manager->create(['name' => 'greeting', 'template' => 'Hi there, {{ name }}!']);

// Get latest (version 2)
$template = $manager->get('greeting');

// Get specific version
$template = $manager->get('greeting', version: 1);

// List all versions
$versions = $manager->all('greeting');
```

## Variable Validation

```php
$template = $manager->get('summarize-text');

// Check required variables
$template->requiredVariables(); // ['text', 'tone']

// Validate provided variables
$template->validateVariables(['text' => 'Hello world']); // false (missing 'tone')
$template->validateVariables(['text' => 'Hello', 'tone' => 'casual']); // true
```

## Using Prompts

```php
use MageTech\AIGateway\Support\Facades\AI;

$response = AI::prompt('summarize-text')
    ->with([
        'text' => 'Long article content here...',
        'tone' => 'professional',
    ])
    ->generate();
```

## Caching

When using cache storage, prompts are cached for the configured TTL:

```php
'prompts' => [
    'storage' => 'cache', // or 'database'
    'cache_ttl' => 60, // minutes
],
```

Cache is automatically cleared when prompts are updated.
