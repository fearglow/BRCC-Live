# BRCC Live

This repository includes a PHPUnit configuration for running integration tests against WordPress with all plugins and themes located in `wp-content`.

## Running Tests

1. Install WordPress and the test suite (this only needs to be done once):

```bash
bash tests/install-wp-tests.sh wordpress_test db_user db_pass localhost latest
```

Replace `wordpress_test`, `db_user` and `db_pass` with your MySQL credentials.

2. Execute the tests from the repository root:

```bash
phpunit
```

This boots WordPress using the `wp-content` directory from the repository so that all plugins and themes are available for testing.
