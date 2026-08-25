# MTS Laravel Packages - Packagist Preparation Script
# This script extracts packages from the monorepo and prepares them for Packagist

$ErrorActionPreference = "Stop"
$sourceBase = "I:\xampp8212\htdocs\MTS-Laravel-Packages\packages"
$outputBase = "I:\xampp8212\htdocs\MTS-Packages"

# Packages to extract
$packages = @(
    @{
        name = "laravel-query-toolkit"
        source = "query-toolkit"
        description = "Enterprise-grade query builder for Laravel APIs with filtering, sorting, searching, pagination, and relationship support."
        php = "^8.2"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    },
    @{
        name = "laravel-api-toolkit"
        source = "api-toolkit"
        description = "Standardized enterprise API responses, errors and API architecture for Laravel."
        php = "^8.2"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    },
    @{
        name = "laravel-webhooks"
        source = "webhooks"
        description = "Enterprise-grade inbound and outbound webhook infrastructure for Laravel."
        php = "^8.2"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    },
    @{
        name = "laravel-import-export"
        source = "import-export"
        description = "Enterprise import/export engine for Laravel with streaming, queue processing, and progress tracking."
        php = "^8.2"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    },
    @{
        name = "laravel-workflow"
        source = "workflow"
        description = "Enterprise workflow engine and approval automation for Laravel."
        php = "^8.2"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    },
    @{
        name = "laravel-ai-gateway"
        source = "ai-gateway"
        description = "Production AI governance layer for Laravel — prompt management, model routing, cost tracking, rate limiting, caching, audit logging, and tenant quotas."
        php = "^8.3"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    },
    @{
        name = "laravel-devtools"
        source = "devtools"
        description = "Local-only Laravel developer dashboard and diagnostic toolkit — application info, performance metrics, security audit, package status, and artisan commands."
        php = "^8.2"
        laravel = "^11.0|^12.0|^13.0"
        testbench = "^8.0|^9.0|^10.0"
        pest = "^2.0|^3.0"
        phpunit = "^10.0|^11.0|^12.0"
    }
)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "MTS Laravel Packages - Packagist Prep" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Create output directory
if (-not (Test-Path $outputBase)) {
    New-Item -ItemType Directory -Path $outputBase -Force | Out-Null
    Write-Host "Created output directory: $outputBase" -ForegroundColor Green
}

foreach ($package in $packages) {
    Write-Host ""
    Write-Host "Processing: $($package.name)" -ForegroundColor Yellow
    Write-Host "-" * 40
    
    $sourcePath = Join-Path $sourceBase $package.source
    $destPath = Join-Path $outputBase $package.name
    
    # Check if source exists
    if (-not (Test-Path $sourcePath)) {
        Write-Host "  ERROR: Source directory not found: $sourcePath" -ForegroundColor Red
        continue
    }
    
    # Remove existing destination if it exists
    if (Test-Path $destPath) {
        Write-Host "  Removing existing destination..." -ForegroundColor Gray
        Remove-Item -Path $destPath -Recurse -Force
    }
    
    # Copy package files
    Write-Host "  Copying package files..." -ForegroundColor Gray
    
    # Create destination directory
    New-Item -ItemType Directory -Path $destPath -Force | Out-Null
    
    # Copy all items except vendor, node_modules, .git
    $items = Get-ChildItem -Path $sourcePath -Exclude "vendor", "node_modules", ".git"
    foreach ($item in $items) {
        $destItem = Join-Path $destPath $item.Name
        if ($item.PSIsContainer) {
            Copy-Item -Path $item.FullName -Destination $destItem -Recurse -Force
        } else {
            Copy-Item -Path $item.FullName -Destination $destItem -Force
        }
    }
    
    # Read and fix composer.json
    $composerPath = Join-Path $destPath "composer.json"
    if (Test-Path $composerPath) {
        Write-Host "  Fixing composer.json..." -ForegroundColor Gray
        $composer = Get-Content $composerPath -Raw | ConvertFrom-Json
        
        # Remove version field
        if ($composer.PSObject.Properties['version']) {
            $composer.PSObject.Properties.Remove('version')
            Write-Host "    - Removed version field" -ForegroundColor Gray
        }
        
        # Update homepage URL to point to individual repo
        $composer.homepage = "https://github.com/magetechsol/$($package.name)"
        
        # Standardize PHP version
        $composer.require.php = $package.php
        
        # Standardize Laravel version requirements
        $illuminatePackages = @("illuminate/contracts", "illuminate/database", "illuminate/http", "illuminate/support", "illuminate/routing", "illuminate/queue")
        foreach ($pkg in $illuminatePackages) {
            if ($composer.require.PSObject.Properties[$pkg]) {
                $composer.require.$pkg = $package.laravel
            }
        }
        
        # Standardize dev dependencies
        if ($composer.'require-dev'.PSObject.Properties['orchestra/testbench']) {
            $composer.'require-dev'.'orchestra/testbench' = $package.testbench
        }
        if ($composer.'require-dev'.PSObject.Properties['pestphp/pest']) {
            $composer.'require-dev'.'pestphp/pest' = $package.pest
        }
        if ($composer.'require-dev'.PSObject.Properties['phpunit/phpunit']) {
            $composer.'require-dev'.'phpunit/phpunit' = $package.phpunit
        }
        
        # Save fixed composer.json
        $composer | ConvertTo-Json -Depth 10 | Set-Content -Path $composerPath -Encoding UTF8
        Write-Host "    - Updated homepage, PHP/Laravel versions" -ForegroundColor Gray
    }
    
    # Remove vendor directory if it exists
    $vendorPath = Join-Path $destPath "vendor"
    if (Test-Path $vendorPath) {
        Write-Host "  Removing vendor directory..." -ForegroundColor Gray
        Remove-Item -Path $vendorPath -Recurse -Force
    }
    
    # Remove composer.lock if it exists
    $lockPath = Join-Path $destPath "composer.lock"
    if (Test-Path $lockPath) {
        Write-Host "  Removing composer.lock..." -ForegroundColor Gray
        Remove-Item -Path $lockPath -Force
    }
    
    # Initialize git repository
    Write-Host "  Initializing git repository..." -ForegroundColor Gray
    Push-Location $destPath
    git init
    git add .
    git commit -m "feat: initial release of $($package.name)"
    Pop-Location
    
    Write-Host "  SUCCESS: Package prepared at $destPath" -ForegroundColor Green
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "All packages prepared!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Create GitHub repositories at https://github.com/organizations/magetechsol/repositories/new"
Write-Host "2. Run the publish script to push to GitHub"
Write-Host "3. Submit to Packagist at https://packagist.org/packages/submit"
