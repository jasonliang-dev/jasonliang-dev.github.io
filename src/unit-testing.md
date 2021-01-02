---
title: Write Your Own C++ Unit Testing Library
date: 2022-01-03
---

By abusing the fact that you can run code before `main()` by putting it in the
contructor of a global variable, you can make a very small, yet serviceable,
unit testing library in C++:

```c++
#include "test.h"

TEST(OnePlusOne) { EXPECT(1 + 1 == 2); }
TEST(OnePlusOneFail) { EXPECT(1 + 1 == 3); }

int main() {
    int err_count = run_all_tests();
    return err_count == 0 ? 0 : -1;
}
```

The output:

```
[ PASS ] OnePlusOne
[ FAIL ] OnePlusOneFail: 1 + 1 == 3

1 out of 2 tests passed
```

## Behind The Scenes

`test.h` takes each `TEST()` and stores them in a vector of `TestCase` structs.

```c++
struct TestCase {
    const char *name;
    void (*run)(const char *&);
};

static std::vector<TestCase> global_all_tests;
```

Let's take `TEST(OnePlusOne) { EXPECT(1 + 1 == 2); }` as an example: The `name`
for this test case will be set to `"OnePlusOne"`, and `run` will become a
pointer to a function that executes `EXPECT(1 + 1 == 2)` when invoked.
This test case gets added to `global_all_tests`. The `run_all_tests()` function
goes through each test case in the vector and invokes run on each of them.

Here is the implementation of the `TEST()` and `EXCEPT()` macros:

```c++
#define TEST(name)                                                             \
    void(_test_##name)(const char *&);                                         \
    static void _init_##name() {                                               \
        global_all_tests.push_back({#name, _test_##name});                     \
    }                                                                          \
    struct _struct_##name {                                                    \
        _struct_##name() { _init_##name(); }                                   \
    };                                                                         \
    static _struct_##name _var_##name;                                         \
    void(_test_##name)(const char *&_test_reason)

#define EXPECT(cond)                                                           \
    if (!(cond)) {                                                             \
        _test_reason = #cond;                                                  \
        return;                                                                \
    }
```

To break this down:
  1. `void(_test_##name)(const char *&);` is the function signature for the test case.
     The implementation is for the user to fill in, which is why the `TEST()` macro
     ends with the same function, except the body is missing.
  2. `static void _init_##name() { ... }` is a function that will get called before `main()`.
     The function `_test_##name` is added to `global_all_tests`.
  3. `struct _struct_##name { ... };` is a type that calls the function above in its
     constructor.
  4. `static _struct_##name _var_##name;` actually runs the `_init_##name()` function
     by creating a variable of the type that was written above.
  5. `void(_test_##name)(const char *&_test_reason)` is the actual function for the test.
     To check if a test passed, each test function takes a string. The value of the
     string passed in will be null, and the `EXCEPT()` macro sets the string whenever
     the test fails. The test passes when the string remains null.

That's pretty much it. Create a `run_all_tests()` and create `const char *` variable.
Make a loop and pass the variable to each test case. For each test, check if the
variable gets mutated to see if it passed or failed.

## The Full Source Code

Here is `test.h` in its entirety. It's only 47 lines.

```c++
#pragma once

#include <cstdio>
#include <vector>

struct TestCase {
    const char *name;
    void (*run)(const char *&);
};

static std::vector<TestCase> global_all_tests;

#define TEST(name)                                                             \
    void(_test_##name)(const char *&);                                         \
    static void _init_##name() {                                               \
        global_all_tests.push_back({#name, _test_##name});                     \
    }                                                                          \
    struct _struct_##name {                                                    \
        _struct_##name() { _init_##name(); }                                   \
    };                                                                         \
    static _struct_##name _var_##name;                                         \
    void(_test_##name)(const char *&_test_reason)

#define EXPECT(cond)                                                           \
    if (!(cond)) {                                                             \
        _test_reason = #cond;                                                  \
        return;                                                                \
    }

static int run_all_tests() {
    const char *reason = nullptr;
    int passed = 0;

    for (const auto test : global_all_tests) {
        test.run(reason);
        if (reason) {
            printf("[ FAIL ] %s: %s\n", test.name, reason);
            reason = nullptr;
        } else {
            printf("[ PASS ] %s\n", test.name);
            passed++;
        }
    }

    printf("\n%d out of %u tests passed\n", passed, global_all_tests.size());
    return global_all_tests.size() - passed;
}
```
