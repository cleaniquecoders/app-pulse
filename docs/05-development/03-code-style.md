# Code Style

AppPulse follows Laravel and PHP community standards.

## PHP Coding Standards

We use [Laravel Pint](https://laravel.com/docs/pint) for code styling.

### Run Code Formatter

```bash
composer format
```

Or:

```bash
./vendor/bin/pint
```

## Static Analysis

We use [PHPStan/Larastan](https://github.com/nunomaduro/larastan) for static analysis.

### Run Static Analysis

```bash
composer analyse
```

Or:

```bash
./vendor/bin/phpstan analyse
```

## Style Guidelines

- Use type hints wherever possible
- Add DocBlocks for complex methods
- Keep methods focused and single-purpose
- Use descriptive variable names
- Follow PSR-12 coding standard
- Use Laravel conventions and patterns

## Naming Conventions

- **Models**: Singular, PascalCase (e.g., `Monitor`)
- **Controllers**: PascalCase with Controller suffix (e.g., `MonitorController`)
- **Actions**: Verb + Noun (e.g., `CheckMonitor`)
- **Events**: Past tense (e.g., `MonitorUptimeChanged`)
- **Jobs**: Verb + Noun + Job (e.g., `CheckMonitorJob`)
