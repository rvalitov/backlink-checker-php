# Contributing to Backlink Checker PHP

Thank you for considering contributing to this project!
To contribute, please follow these guidelines.

## 1. Fork the Repository and Create Your Branch

1. Fork the repository.
2. Create a new branch from `master` for your changes.

## 2. Adhere to Existing Style Conventions

Ensure your code adheres to the existing style conventions used in the project.
You can use the following command to check the style conventions:

```bash
npm run style
```

If you see any errors or warnings, please fix them before opening a pull request.

You can use the following command to fix the Markdown style issues:

```bash
npm run markdown:fix
```

To fix other style issues, you can use the following command:

```bash
npm run prettier:fix
```

If you modify the dependencies in `package.json` or `composer.json` or modify these files manually,
please keep the file sorted:

```bash
npm run sort
```

## 3. Run Tests to Ensure Changes Do Not Break Functionality

First run the tests for static PHP code analysis:

```bash
npm run php
```

If you see any errors or warnings, please fix them before opening a pull request.
Some errors or warnings can be fixed automatically using the following command:

```bash
npm run phpcs-fixer:fix
```

Then run the PHPUnit tests for the PHP code. First start the server:

```bash
npm run start-server
```

Then run the tests.
Be sure to keep the server running while running the tests.
You can run the tests using the following command in a new terminal:

```bash
npm run test
```

This command will run all the tests, including the static analysis checks.

## 4. Add New Tests for Your Changes if Applicable

* Add new tests for your changes if applicable.
* Ensure the tests cover all the possible scenarios and edge cases.
* The tests should be added to the `tests` directory.
* Sample data for the tests should be added to the `tests/data` directory.

## 5. Open a Pull Request with a Clear Description

Open a pull request with a clear description of your changes and the problem they solve.
If your pull request fixes an issue, please reference the issue in the pull request description.

## 6. Update the Documentation

If you add new features or modify existing features, please update the `README.md` file.

## 7. Resources

* [Using Pull Requests](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests)
* [GitHub Flow](https://docs.github.com/en/get-started/using-github/github-flow)
