# Use Cases

Industry-specific examples for the MTS AI Gateway.

## SaaS Application

Multi-tenant SaaS with per-tenant quotas:

```php
// Define support prompt
Prompt::create([
    'name' => 'customer-support',
    'version' => 1,
    'template' => 'You are a support agent for {{ company }}. Help the customer: {{ message }}',
    'model' => 'gpt-4o',
    'temperature' => 0.3,
]);

// Use with tenant isolation
$response = AI::prompt('customer-support')
    ->forTenant($tenant->id)
    ->forUser($user->id)
    ->with([
        'company' => $tenant->name,
        'message' => $request->input('message'),
    ])
    ->generate();
```

## CRM

AI-powered CRM with contact enrichment:

```php
Prompt::create([
    'name' => 'contact-enrichment',
    'version' => 1,
    'template' => 'Enrich this contact profile: {{ name }}, {{ company }}. Provide insights about their role and potential needs.',
    'model' => 'gpt-4o',
]);

$response = AI::prompt('contact-enrichment')
    ->with([
        'name' => $contact->name,
        'company' => $contact->company,
    ])
    ->usingModel('gpt-4o')
    ->generate();
```

## E-commerce

Product descriptions and recommendations:

```php
// Product description
Prompt::create([
    'name' => 'product-description',
    'version' => 1,
    'template' => 'Write a compelling product description for {{ product_name }}. Category: {{ category }}. Price: {{ price }}. Features: {{ features }}',
    'model' => 'gpt-4o',
    'temperature' => 0.7,
]);

// Product recommendations
Prompt::create([
    'name' => 'product-recommendations',
    'version' => 1,
    'template' => 'Based on these purchased items: {{ products }}, recommend 5 similar products from our catalog: {{ catalog }}',
    'model' => 'gpt-4o-mini',
]);
```

## AI Assistant

Personal AI assistant with context:

```php
Prompt::create([
    'name' => 'assistant',
    'version' => 1,
    'template' => 'You are a helpful assistant. User context: {{ context }}. Previous conversation: {{ history }}. Current request: {{ request }}',
    'model' => 'gpt-4o',
    'temperature' => 0.5,
]);

$response = AI::prompt('assistant')
    ->with([
        'context' => $user->preferences,
        'history' => $conversation->getRecentMessages(),
        'request' => $userMessage,
    ])
    ->usingProvider('anthropic')
    ->generate();
```

## Content Generation

Blog posts, social media, marketing:

```php
Prompt::create([
    'name' => 'blog-post',
    'version' => 1,
    'template' => 'Write a {{ word_count }} word blog post about {{ topic }}. Tone: {{ tone }}. Include these keywords: {{ keywords }}',
    'model' => 'gpt-4o',
    'temperature' => 0.8,
    'max_tokens' => 2000,
]);

$response = AI::prompt('blog-post')
    ->with([
        'word_count' => '1000',
        'topic' => 'AI in Healthcare',
        'tone' => 'professional',
        'keywords' => 'machine learning, diagnostics, patient care',
    ])
    ->generate();
```

## RAG (Retrieval-Augmented Generation)

Combine retrieval with generation:

```php
// Retrieve relevant documents
$documents = $vectorStore->search($query, limit: 5);

Prompt::create([
    'name' => 'rag-qa',
    'version' => 1,
    'template' => 'Based on these documents: {{ documents }}, answer this question: {{ question }}. If the answer is not in the documents, say so.',
    'model' => 'gpt-4o',
    'temperature' => 0.2,
]);

$response = AI::prompt('rag-qa')
    ->with([
        'documents' => $documents->pluck('content')->implode("\n\n"),
        'question' => $userQuestion,
    ])
    ->generate();
```

## AI Agents

Autonomous agents with tool calling:

```php
Prompt::create([
    'name' => 'research-agent',
    'version' => 1,
    'template' => 'You are a research agent. Analyze the task: {{ task }}. Use available tools to gather information and provide a comprehensive analysis.',
    'model' => 'gpt-4o',
    'temperature' => 0.3,
]);

$response = AI::prompt('research-agent')
    ->with(['task' => $researchTask])
    ->withOptions([
        'tools' => [
            ['type' => 'function', 'function' => ['name' => 'web_search']],
            ['type' => 'function', 'function' => ['name' => 'read_document']],
        ],
    ])
    ->generate();
```

## Tool Calling

Structured tool execution:

```php
Prompt::create([
    'name' => 'data-analysis',
    'version' => 1,
    'template' => 'Analyze this dataset and perform: {{ action }}. Data: {{ data }}',
    'model' => 'gpt-4o',
]);

$response = AI::prompt('data-analysis')
    ->with([
        'action' => 'calculate average and identify outliers',
        'data' => $dataset->toJson(),
    ])
    ->withOptions([
        'tools' => [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'calculate',
                    'description' => 'Perform mathematical calculations',
                ],
            ],
        ],
    ])
    ->generate();
```

## Multi-Provider Strategy

Use different providers for different tasks:

```php
// Fast responses for chat
$response = AI::prompt('chat')
    ->usingProvider('groq')
    ->usingModel('groq-llama-3.1-8b')
    ->generate();

// High quality for content
$response = AI::prompt('blog-post')
    ->usingProvider('anthropic')
    ->usingModel('claude-3-5-sonnet-20241022')
    ->generate();

// Cost-effective for bulk processing
$response = AI::prompt('data-labeling')
    ->usingProvider('deepseek')
    ->usingModel('deepseek-chat')
    ->generate();
```
