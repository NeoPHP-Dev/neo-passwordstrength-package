# PasswordStrength Package

A configurable password strength validator for NeoPHP. Define your
rules once, in PHP, and use them for real server-side validation — the
optional Twig macro and JS engine read the exact same configuration, so
there is never a mismatch between what the UI shows and what the server
actually enforces.

---

## Structure

```
passwordstrength-package/
├── composer.json
├── README.md
└── src/
    ├── NeoPasswordStrengthPackage.php
    ├── Service/
    │   ├── PasswordStrengthFactory.php
    │   └── PasswordStrengthConfig.php
    ├── Assets/
    │   ├── css/passwordstrength.css
    │   └── js/
    │       ├── strength-engine.js
    │       └── passwordstrength.js
    └── Templates/
        └── components/
            └── PasswordStrength.macro.html.twig
```

---

## Three levels of usage

This package does not force any particular UI on you. Pick the level
that fits your project.

### Level 1 — Full package: validation + default UI

The simplest path. Configure the rules once, pass the config to the
macro, get a password field with a live strength bar and a criteria
checklist, styled and functional out of the box.

```php
public function registerForm(PasswordStrengthFactory $factory): Response
{
    $passwordConfig = $factory->configure()
        ->setMinLength(10)
        ->requireUppercase()
        ->requireNumber()
        ->requireSpecialChar();

    return $this->render('register.html.twig', ['passwordConfig' => $passwordConfig]);
}
```

```twig
{% import '@PasswordStrength/components/PasswordStrength.macro.html.twig' as PasswordStrength %}
<link rel="stylesheet" href="/packages-assets/PasswordStrength/css/passwordstrength.css">

{{ PasswordStrength.render('password', passwordConfig) }}

<script type="module" src="/packages-assets/PasswordStrength/js/passwordstrength.js"></script>
```

Then validate the submission with the exact same rules:

```php
public function register(Request $request, PasswordStrengthFactory $factory): Response
{
    $config = $factory->configure()->setMinLength(10)->requireUppercase()->requireNumber()->requireSpecialChar();

    $result = $config->validate($request->getPost('password', ''));

    if (!$result['valid']) {
        return $this->render('register.html.twig', ['errors' => $result['errors']]);
    }

    // ...
}
```

**Tip:** define the config once in a shared method/service so the same
rules are never typed twice between the form page and the submit
handler.

### Level 2 — Validation only, no UI at all

Use `PasswordStrengthConfig` as a plain PHP validator. Never touch the
macro, the CSS, or the JS — build your own `<input>` with your own
styling, and just call `validate()` on submit.

```php
$config = $factory->configure()->setMinLength(12)->requireSpecialChar();
$result = $config->validate($submittedPassword);

if (!$result['valid']) {
    foreach ($result['errors'] as $error) {
        // your own error handling
    }
}
```

### Level 3 — Your own UI, powered by the same JS engine

If you want a live strength indicator but with completely custom
markup/styling, skip the macro and `passwordstrength.js`, and import
`strength-engine.js` directly — it's a pure function with no DOM
dependency:

```js
import { evaluatePassword } from '/packages-assets/PasswordStrength/js/strength-engine.js';

const config = /* the same shape as PasswordStrengthConfig::toArray(), e.g. rendered as JSON from PHP */;
const result = evaluatePassword(myPasswordInput.value, config);
// { score: 3, criteria: { minLength: true, uppercase: true, ... }, errors: [] }

// build your own bar, your own checklist, however you like
```

---

## Installation

```bash
php bin/neo package:require neophp/passwordstrength-package --project=MyProject
```

Register it in the project's `Config/app.config.php`:

```php
return [
    // ...
    'packages' => [
        \Vendor\NeoPHP\PasswordStrengthPackage\NeoPasswordStrengthPackage::class,
    ],
];
```

No configuration file, no migration.

---

## `PasswordStrengthConfig` API

| Method | Purpose |
|---|---|
| `setMinLength(int)` | Default `8` |
| `setMaxLength(int)` | Default `null` (no limit) |
| `requireUppercase(bool = true)` | Default `false` |
| `requireLowercase(bool = true)` | Default `false` |
| `requireNumber(bool = true)` | Default `false` |
| `requireSpecialChar(bool = true)` | Default `false` |
| `setSpecialChars(list<string>)` | Default `! @ # $ % ^ & * ? - _` |
| `validate(string): array{valid, errors, score}` | Real server-side validation |
| `toArray()` / `toJson()` | The shape consumed by the JS engine |

`score` is a 0–4 strength estimate based on length and character
variety — it is informational and independent from whether the
configured required-rules actually pass (a password can score 4 while
still failing `requireSpecialChar()` if that rule is on and no special
character is present).

---

## Important: `passwordstrength.js` is an ES module

The `<script>` tag loading it must include `type="module"`, since it
uses `import`/`export`:

```html
<script type="module" src="/packages-assets/PasswordStrength/js/passwordstrength.js"></script>
```

`strength-engine.js`, used on its own (Level 3), is also an ES module —
import it with a bundler or directly via `<script type="module">` in
your own code.

---

## Theming

Every visual value in the default macro is a CSS custom property scoped
to `.ps-wrapper`:

```css
.ps-wrapper {
    --ps-bg: #161923;
    --ps-border: #2d3342;
    --ps-text: #e5e7eb;
    --ps-text-muted: #9ca3af;
    --ps-track-bg: #1b2030;
}
```

Strength bar colors (weak → strong) are currently set directly in
`passwordstrength.js`, not as CSS variables — override them by editing
the `scoreColors` array in your own copy of the script if needed, or
build your own UI with Level 3 for full control.

---

## What this package does *not* do

- No check against known-compromised password lists (e.g. Have I Been
  Pwned) — that would require an external API call, out of scope for
  this package
- No password history / reuse prevention
- The scoring heuristic is simple (length + character variety) — it is
  not a cryptographic entropy calculation

---

## License

MIT