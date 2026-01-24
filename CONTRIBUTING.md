# Contributing to WooSpeed Analytics

First off, thank you for considering contributing to WooSpeed Analytics! It's people like you that make WooSpeed such a great tool.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Messages](#commit-messages)
- [Submitting Changes](#submitting-changes)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)

---

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to carlos@carlosindriago.com.

**Our Pledge:**
- Be inclusive and respectful
- Be constructive and helpful
- Focus on what is best for the community
- Show empathy towards other community members

---

## How Can I Contribute?

### Reporting Bugs

1. **Check existing issues** - Search for similar problems
2. **Use the bug report template** - Include all requested information
3. **Provide steps to reproduce** - Clear reproduction steps are essential
4. **Include environment details** - PHP version, WP version, WooCommerce version
5. **Add screenshots** - If applicable, add screenshots to clarify

### Suggesting Enhancements

1. **Check existing feature requests** - Avoid duplicates
2. **Explain the use case** - Why would this feature be useful?
3. **Propose a solution** - How do you envision it working?
4. **Consider trade-offs** - What are the pros/cons?

### Writing Code

1. Fork the repository
2. Create a feature branch
3. Write your code (follow our standards)
4. Add tests for your changes
5. Ensure all tests pass
6. Submit a pull request

---

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js 18+ (for E2E tests)
- Docker (optional, for containerized development)

### Setup Steps

```bash
# 1. Fork and clone the repository
git clone https://github.com/YOUR_USERNAME/woospeed-analytics.git
cd woospeed-analytics

# 2. Install dependencies
composer install

# 3. Setup WordPress test environment (optional)
bash tests/install-wp-tests.sh wordpress root root localhost latest

# 4. Run tests to verify setup
composer test

# 5. Run static analysis
composer analyze
```

### Local Development with WordPress

#### Option 1: WP Local (Recommended)

```bash
# Install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Start WordPress
wp local start

# Install plugin
wp plugin install . --activate
```

#### Option 2: Docker

```bash
# Start development environment
docker-compose up -d

# Access WordPress at http://localhost:8080
# Login: admin / password
```

---

## Coding Standards

### PHP Standards

We follow **PSR-12** with strict PHP 8.1+ types and **PHPStan Level 9**.

#### Type Hints (Required)

```php
// ✅ GOOD - Complete type hints
public function save_report(int $order_id, float $total, string $date): bool
{
    // ...
}

// ❌ BAD - Missing return type
public function save_report(int $order_id, float $total, string $date)
{
    // ...
}
```

#### PHPDoc (Required)

```php
/**
 * Save order report to database
 *
 * @param int $order_id WooCommerce order ID
 * @param float $total Order total amount
 * @param string $date Report date in Y-m-d format
 * @return bool True on success, false on failure
 */
public function save_report(int $order_id, float $total, string $date): bool
{
    // ...
}
```

#### Error Handling

```php
// ✅ GOOD - Try-catch in critical hooks
public function sync_order(int $order_id): void
{
    try {
        $order = wc_get_order($order_id);
        // ... sync logic
    } catch (Exception $e) {
        error_log(sprintf('[WooSpeed] Error: %s', $e->getMessage()));
        // Don't throw - allow operation to continue
    }
}

// ❌ BAD - No error handling
public function sync_order(int $order_id): void
{
    $order = wc_get_order($order_id);
    // ... sync logic (could crash entire order process)
}
```

### JavaScript Standards

We use **ES6+** with private fields and async/await.

#### Class Structure

```javascript
// ✅ GOOD - ES6 class with private fields
class WooSpeedDashboard {
    #chartCanvas = null;
    #currentPreset = 'month_to_date';

    async #loadDashboard() {
        const response = await fetch(url);
        const data = await response.json();
        this.#updateKPIs(data.kpis);
    }

    #updateKPIs(kpis) {
        // ...
    }
}

// ❌ BAD - Old prototype pattern
function WooSpeedDashboard() {
    this.chartCanvas = null;
    this.loadDashboard = function() {
        // ...
    };
}
```

#### Async/Await

```javascript
// ✅ GOOD - async/await
async #loadDashboard() {
    try {
        const response = await fetch(url);
        const data = await response.json();
        this.#updateUI(data);
    } catch (error) {
        console.error('[WooSpeed] Error:', error);
    }
}

// ❌ BAD - Promise chains
#loadDashboard() {
    fetch(url)
        .then(res => res.json())
        .then(data => this.#updateUI(data))
        .catch(err => console.error(err));
}
```

### SQL Standards

Always use prepared statements:

```php
// ✅ GOOD - Prepared statement
$result = $wpdb->query($wpdb->prepare(
    "INSERT INTO $table (order_id, total) VALUES (%d, %f)",
    $order_id,
    $total
));

// ❌ BAD - Direct interpolation (SQL injection risk)
$result = $wpdb->query("INSERT INTO $table (order_id, total) VALUES ($order_id, $total)");
```

---

## Testing

### Unit Tests

Write tests for all new functionality:

```php
class MyNewTest extends TestCase {
    public function test_my_new_feature(): void
    {
        $result = $this->repository->myNewMethod('test');
        $this->assertEquals('expected', $result);
    }
}
```

### Run Tests

```bash
# Unit tests only
composer test

# With coverage report
composer test:coverage

# Integration tests (requires WP test suite)
composer test:integration
```

### Test Goals

- **Statements**: 80%+
- **Methods**: 85%+
- **Classes**: 90%+

---

## Commit Messages

We use **Conventional Commits** format:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `refactor`: Code refactoring (no functional change)
- `docs`: Documentation only
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `perf`: Performance improvement
- `security`: Security fix

### Examples

```
feat(dashboard): add weekday sales chart

- Added bar chart showing sales by day of week
- Data fetched from get_weekday_sales() method
- Chart uses different colors for each weekday

Closes #123
```

```
fix(migration): prevent timeout on large datasets

- Reduced batch size from 100 to 50 orders
- Added progress bar during migration
- Migration can now be paused and resumed

Fixes #89
```

---

## Submitting Changes

### Pull Request Process

1. **Update your branch** with latest develop:
   ```bash
   git checkout develop
   git pull origin develop
   git checkout feature/my-feature
   git rebase develop
   ```

2. **Run all checks**:
   ```bash
   composer test
   composer analyze
   composer cs-check
   ```

3. **Push your changes**:
   ```bash
   git push origin feature/my-feature
   ```

4. **Create Pull Request** on GitHub

### Pull Request Checklist

- [ ] Code follows our coding standards
- [ ] Tests added/updated and pass
- [ ] PHPStan shows no errors
- [ ] Documentation updated (README, PHPDoc)
- [ ] Commit messages follow Conventional Commits
- [ ] PR description clearly explains changes
- [ ] Linked to relevant issue (e.g., "Fixes #123")

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Performance improvement
- [ ] Code refactoring
- [ ] Documentation

## Testing
- [ ] Unit tests added/updated
- [ ] All tests passing
- [ ] Manual testing completed

## Checklist
- [ ] PHPStan Level 9 passing
- [ ] PSR-12 compliant
- [ ] PHPDoc complete
- [ ] No backwards compatibility breaks (or documented)

## Related Issues
Fixes #123
Related to #456
```

---

## Reporting Bugs

### Bug Report Template

```markdown
**Description**
Clear description of the problem

**Environment**
- PHP Version: 8.1.2
- WordPress Version: 6.1.1
- WooCommerce Version: 7.3.0
- Plugin Version: 3.0.0

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
Description of what you expected to happen

**Actual Behavior**
Description of what actually happened

**Screenshots**
If applicable, add screenshots

**Additional Context**
Any other relevant information (logs, browser console, etc.)
```

---

## Suggesting Enhancements

### Feature Request Template

```markdown
**Problem Description**
Clear description of the problem this feature would solve

**Proposed Solution**
Description of the proposed solution

**Alternatives Considered**
Description of any alternative solutions or features you've considered

**Additional Context**
Any other context, screenshots, or examples
```

---

## Recognition

Contributors will be acknowledged in:
- README.md contributors section
- Release notes for significant contributions
- CREDITS.md file (for major contributors)

---

## Getting Help

- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and community discussion
- **Email**: support@carlosindriago.com

---

## License

By contributing, you agree that your contributions will be licensed under the **GPL v2.0**.

---

**Thank you for contributing to WooSpeed Analytics! 🚀**
