---
name: test-naming
description: "Generate clean, professional test structures for Laravel Pest. Use when writing test files (*Test.php), creating describe/it blocks, or reviewing test naming conventions. Ensures tests read as technical specifications with declarative naming."
---

# Test Naming

Generate test structures that serve as technical specifications.

## Quick Start

Preview test tree without execution:

```bash
php artisan test --list
```

## Structure Rules

**`describe`**: Class, feature, or endpoint name.

**Nested `describe`**: Use "when [condition]" or "given [state]" for scenarios.

**`it`**: Complete the sentence "It..." as a factual statement.

## Code Style Rules

**Never use `$this`**: Use explicit function imports from `Pest\Laravel` for better IDE inference. Redundancy per test case is acceptable.

```php
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertDatabaseCount;
```

## Naming Rules

| Rule                              | Rationale                                         |
| --------------------------------- | ------------------------------------------------- |
| Never use "should"                | Redundant - "It should return X" → "It returns X" |
| Use third-person singular present | `returns`, `throws`, `updates`                    |
| State outcomes as facts           | Tests assert truths, not possibilities            |

## Vocabulary Reference

| Category   | Verbs                                                                |
| ---------- | -------------------------------------------------------------------- |
| State      | `returns`, `provides`, `initializes`, `contains`, `exposes`          |
| Action     | `updates`, `persists`, `notifies`, `dispatches`, `emits`, `triggers` |
| Error      | `throws`, `rejects`, `ignores`, `denies`, `fails`                    |
| Validation | `validates`, `requires`, `matches`, `enforces`                       |
| HTTP       | `redirects`, `responds with`, `authenticates`, `authorizes`          |
| Database   | `creates`, `deletes`, `attaches`, `syncs`, `soft deletes`            |

## Examples

### Feature Test

```php
use function Pest\Laravel\postJson;

describe('PaymentController', function () {
    describe('store', function () {
        describe('when the credit card is expired', function () {
            it('returns a 422 status code', function () {
                $expiredCard = ['card_number' => '4111111111111111', 'exp_date' => '01/20'];

                postJson('/api/payments', $expiredCard)
                    ->assertUnprocessable()
                    ->assertJsonPath('error', 'Card expired');
            });

            it('does not create a payment record', function () {
                $expiredCard = ['card_number' => '4111111111111111', 'exp_date' => '01/20'];

                postJson('/api/payments', $expiredCard);

                expect(Payment::count())->toBe(0);
            });
        });

        describe('given a valid payment method', function () {
            it('creates a payment record', function () {
                $validCard = ['card_number' => '4111111111111111', 'exp_date' => '12/30'];

                postJson('/api/payments', $validCard)
                    ->assertCreated();

                expect(Payment::count())->toBe(1);
            });

            it('dispatches a PaymentProcessed event', function () {
                Event::fake();
                $validCard = ['card_number' => '4111111111111111', 'exp_date' => '12/30'];

                postJson('/api/payments', $validCard);

                Event::assertDispatched(PaymentProcessed::class);
            });
        });
    });
});
```

### Unit Test

```php
describe('Order', function () {
    describe('calculateTotal', function () {
        describe('when discount is applied', function () {
            it('returns the discounted total', function () {
                $order = new Order(items: [
                    new Item(price: 1000),
                    new Item(price: 500),
                ]);

                $total = $order->calculateTotal(discountPercent: 10);

                expect($total)->toBe(1350);
            });
        });

        describe('when cart is empty', function () {
            it('returns zero', function () {
                $order = new Order(items: []);

                expect($order->calculateTotal())->toBe(0);
            });
        });
    });
});
```

### Higher-Order Tests

```php
describe('User', function () {
    describe('isAdmin', function () {
        it('returns true for admin role')
            ->expect(fn () => User::factory()->admin()->make()->isAdmin())
            ->toBeTrue();

        it('returns false for regular user')
            ->expect(fn () => User::factory()->make()->isAdmin())
            ->toBeFalse();
    });
});
```

## Anti-Patterns

```php
// ❌ WRONG: Uses "should"
it('should create a user', function () {});

// ✅ CORRECT: Declarative
it('creates a user', function () {});

// ❌ WRONG: snake_case test name (PHPUnit style)
test('it_creates_a_user', function () {});

// ✅ CORRECT: Natural language
it('creates a user', function () {});

// ❌ WRONG: Mixing test() and it() inconsistently
describe('UserController', function () {
    test('creates user', function () {});
    it('deletes a user', function () {});
});

// ✅ CORRECT: Consistent use of it()
describe('UserController', function () {
    it('creates a user', function () {});
    it('deletes a user', function () {});
});

// ❌ WRONG: No context grouping
describe('OrderService', function () {
    it('throws when product is out of stock', function () {});
    it('creates an order when stock is available', function () {});
});

// ✅ CORRECT: Grouped by context
describe('OrderService', function () {
    describe('placeOrder', function () {
        describe('when product is out of stock', function () {
            it('throws an InsufficientStockException', function () {});
        });
        describe('when stock is available', function () {
            it('creates an order record', function () {});
            it('decrements the product stock', function () {});
        });
    });
});

// ❌ WRONG: Vague description
it('works', function () {
    post('/users')->assertOk();
});

// ✅ CORRECT: Specific outcome
use function Pest\Laravel\postJson;

it('returns a 201 status code', function () {
    postJson('/api/users', ['name' => 'John', 'email' => 'john@example.com'])
        ->assertCreated();
});

// ❌ WRONG: Using $this (IDE inference issues, prohibited)
it('creates a user', function () {
    $this->postJson('/api/users', ['name' => 'John'])
        ->assertCreated();
});

// ✅ CORRECT: Using explicit imports
use function Pest\Laravel\postJson;

it('creates a user', function () {
    postJson('/api/users', ['name' => 'John'])
        ->assertCreated();
});

// ❌ WRONG: Using $this in beforeEach
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ✅ CORRECT: Using explicit imports in beforeEach
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $user = User::factory()->create();
    actingAs($user);
});

// ❌ WRONG: Not using Pest's expect() API
it('returns the correct count', function () {
    assertEquals(3, User::count()); // PHPUnit style
});

// ✅ CORRECT: Using Pest's expect() API
it('returns the correct count', function () {
    expect(User::count())->toBe(3);
});
```

## Best Practices

### Use Datasets for Parameterized Tests

```php
describe('EmailValidator', function () {
    it('validates email format correctly', function (string $email, bool $expected) {
        expect(EmailValidator::isValid($email))->toBe($expected);
    })->with([
        'valid email' => ['john@example.com', true],
        'missing @' => ['johnexample.com', false],
        'missing domain' => ['john@', false],
    ]);
});
```

### Use beforeEach for Common Setup

```php
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('PostController', function () {
    beforeEach(function () {
        $user = User::factory()->create();
        actingAs($user);
    });

    describe('store', function () {
        it('creates a post', function () {
            postJson('/api/posts', ['title' => 'Test'])
                ->assertCreated();
        });
    });
});
```

### Inline Setup for Simple Tests

```php
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('PostController', function () {
    describe('store', function () {
        it('creates a post', function () {
            $user = User::factory()->create();

            actingAs($user);

            postJson('/api/posts', ['title' => 'Test'])
                ->assertCreated();
        });
    });
});
```

## File Organization

```
tests/
├── Unit/
│   └── Models/
│       └── OrderTest.php
├── Feature/
│   └── Http/
│       └── Controllers/
│           └── PaymentControllerTest.php
└── Pest.php
```
