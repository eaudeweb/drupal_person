# drupal_person

Provides a Drupal Person content type to be reused in projects

# Installation

In `composer.json`:

1. In `"repositories":[]` add:
```
{
    "type": "git",
    "url": "git@github.com:eaudeweb/drupal_person.git"
}
```

2. A SSH Key is required.

3. Run: ```composer require drupal/drupal_person```

## Basic Configuration

- **Contact Person Block** - display a custom block with person's contacts.
- **Contact Person** - controller to contact a person.
- **Contact Person** webform