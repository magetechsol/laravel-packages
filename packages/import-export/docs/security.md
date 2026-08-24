# Security

## Formula Injection Protection

Prevents CSV formula injection by prefixing dangerous characters:

- `=` → `'=`
- `+` → `'+`
- `-` → `'-`
- `@` → `'@`

Enabled by default in config:

```php
'security' => [
    'formula_injection_protection' => true,
],
```

## MIME Validation

Files are validated using `finfo` (not just extension):

```php
'security' => [
    'validate_mime_real' => true,
],
```

## Path Traversal Prevention

Validates file paths with `realpath()`:

```php
'security' => [
    'prevent_path_traversal' => true,
],
```

## File Size Limits

```php
'upload' => [
    'max_file_size' => 10240, // KB
],
```

## Filename Sanitization

Uploaded filenames are sanitized:

```php
'security' => [
    'sanitize_filenames' => true,
],
```

## Authorization

Track who created imports/exports:

```php
Import::make(Product::class)
    ->from($file)
    ->withOptions(['created_by' => auth()->id()])
    ->process();
```

## Production Checklist

- [ ] Enable formula injection protection
- [ ] Set appropriate file size limits
- [ ] Enable MIME validation
- [ ] Enable path traversal prevention
- [ ] Configure queue authentication
- [ ] Set proper file permissions on storage directories
- [ ] Use HTTPS for file uploads
- [ ] Implement rate limiting on upload endpoints
