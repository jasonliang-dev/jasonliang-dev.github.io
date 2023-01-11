C++ is a ridiculously complex language and practically everyone sticks to a
subset of features. I've been using a lot of C++ for side projects that never
see the light of day, and over time, I've developed a style that I like and I
wanted to document it.

## Code Format

Use Clang-Format for code formatting:

```text
BasedOnStyle: LLVM
IndentWidth: 2
AllowShortCaseLabelsOnASingleLine: true
```

## Naming

- Types are PascalCase
- Preprocessor defines are UPPER_CASE, except for commonly used macros like:
  - `#define array_size(a) (sizeof(a) / sizeof(a[0]))`
  - `#define defer(code) /* stuff */` (see [A Defer Statement For C++11](http://www.gingerbill.org/article/2015/08/19/defer-in-cpp/))
- Constants are UPPER_CASE
- Enums types are PascalCase.
  - Raw enum values are TypeName_PascalCase
  - Enum class values are PascalCase
- Functions are snake_case
- Variables are snake_case
  - Public member variables are snake_case
  - Private member variables are m_snake_case
  - Static variables are s_snake_case
  - Global variables are g_snake_case
- File names are either snake_case.cpp or snake_case.h

Example:

```c++
constexpr const char *SETTINGS_FILE_PATH = "./data/settings.ini";

struct String {
  String() = default;
  String(const char *);

private:
  char *m_buf = nullptr;
  i32 m_size = 0;
  i32 m_capacity = 0;
};

enum EntityKind : i32 {
  EntityKind_None,
  EntityKind_Player,
  EntityKind_Bullet,
  EntityKind_Enemy,
};

enum class Direction : i32 {
  North,
  East,
  South,
  West,
};

struct Entity {
  EntityKind kind;
  Direction direction;
  String name;
};

String g_str;

const char *some_function(i32 kind) {
  static char s_str[2048];
  return s_str;
}
```

## Style

- When using single header libraries, create a file `impl.cpp` and add the
  implementation there.

  ```c++
  // impl.cpp

  #define GLAD_GL_IMPLEMENTATION
  #include "glad2.h"

  #define STB_IMAGE_IMPLEMENTATION
  #include "stb_image.h"

  #define STB_TRUETYPE_IMPLEMENTATION
  #include "stb_truetype.h"
  ```

- If there are brackets for a case statement, the `break` goes inside.

  ```c++
  switch (val) {
  case Kind_Foo: {
    i32 n = 0;
    do_stuff(&n);
    printf("%d\n", n);
    break; // <-- here
  }
  }
  ```

- Use lambdas to nest functions.

  > Helps to understand where a function can be used. In the example below,
    `compile` can only be used in `make_shader`

  ```c++
  Shader make_shader(const char *vert, const char *frag) {
    auto compile = [](GLuint type, const char *glsl) -> GLuint {
      // OpenGL stuff
    };

    GLuint vshd = compile(GL_VERTEX_SHADER, vert);
    GLuint fshd = compile(GL_FRAGMENT_SHADER, frag);

    // ...
  }
  ```

- Prefer explicitly sized integers such as `int32_t`.

  > Helps to reason with integer conversion. Use the following type aliases:

  ```c++
  using i8 = int8_t;
  using i16 = int16_t;
  using i32 = int32_t;
  using i64 = int64_t;
  using u8 = uint8_t;
  using u16 = uint16_t;
  using u32 = uint32_t;
  using u64 = uint64_t;
  ```

- Avoid exceptions. Never use `throw`.

  > Read [Exceptions — And Why Odin Will Never Have Them](https://www.gingerbill.org/article/2018/09/05/exceptions-and-why-odin-will-never-have-them/).
  > **TL;DR**: errors are not special, so treat error values like you would any
  > other piece of data.
  >
  > There are several alternatives for error handling without catching exceptions
  > such as using a function that returns:
  > - a boolean
  > - an error code/enum
  > - `std::optional<T>`
  > - `std::pair<T1, T2>`, similar to languages that have mutliple return
  >   values (Go, Odin, Lua)
  > - `std::expected<T, E>`, monadic type similar to Rust's `Result` or
  >   Haskell's `Either`, but I'd like to avoid it since it's too new

- If object creation can fail, use functions instead of constructors.

  ```c++
  // bad
  // if this can fail. how do you let the user know?
  // is there a global error flag?
  // does it throw an exception?
  // remember that we're trying to avoid exceptions
  Bullet::Bullet(Entity *owner);

  // good
  std::optional<Bullet> make_bullet(Entity *owner);
  ```

- Avoid writing destructors.

  > When a destructor is introduced, you'll probably want a copy constructor
  > and copy assignment operator to fix the double free problem when dealing
  > with memory.
  >
  > But copies can be expensive so a move constructor and a move assignment
  > operator should also be added. You better remember to use `std::move` in
  > the right places! Oh wait, you might need functions that deal with data by
  > value, by reference, and by r-value reference. So you'll also want to
  > introduce perfect forwarding.
  >
  > If you have an object X that depends on object Y, then you'll have to
  > make sure that Y stays alive before X is destroyed. And what if you have a
  > cycle? If X depends on Y and Y depends on X, which object should be
  > destroyed first? How would these object get initialized in the first
  > place? Do you combine X and Y into a big mega-object called Z? Or do you
  > use a raw pointer on one of the objects? Are you going to add smart
  > pointers into the mix?
  >
  > Avoid writing destructors. It adds too much complexity. If dealing with
  > RAII types (like the STL containers), use the Rule of Zero. For everything
  > else, just use a function to destroy things.
  > [Defer](http://www.gingerbill.org/article/2015/08/19/defer-in-cpp/)
  > can help with some of the friction that comes with explicit destruction.

- Prefer `std::string_view` over `std::string` and C strings.

  > Unlike C strings, `std::string_view` stores the length. Unlike
  > `std::string`, substring is constant time and does not require memory
  > allocation. Also, copying a string view is very cheap.
  >
  > Unfortunate that string views can't be used a lot of the time because C
  > libraries usually need null terminated strings.

- Avoid `std::array`.

  > C style square bracket arrays work fine. It can be surprisingly delightful
    to use with this `array_size` macro:

  ```c++
  #define array_size(a) (sizeof(a) / sizeof(a[0]))

  // for functions that need a buffer and the buffer size
  sscanf_s(str, "%d %s", &n, buf, (u32)array_size(buf));
  ```

- Avoid `<iostream>`

  > `printf` and friends work very well.

- Use `<stdio.h>`, `<math.h>`, etc, over their C++ counterparts `<cstdio>`,
  `<cmath>`, etc.

  > The idea with the C++ headers was probably to avoid polluting the global
    namespace, but they don't actually do that so there's no benefit.

- Avoid defining types with `class`.

  > Public members should be listed first. It's common to see `class` followed
    by the `public` access specifier, but that's exactly what `struct` does.

  ```c++
  // bad
  class Foo {
  public:
    Foo();
  private:
    i32 m_the_data;
  };

  // good
  struct Foo {
    Foo();
  private:
    i32 m_the_data;
  };
  ```

- Public members go at the top.

  > The public interface/API is the most important information to look for.
    The elements are sorted by importance.

  ```c++
  // bad
  struct Foo {
  private:
    i32 m_the_data;
  public:
    Foo();
  };

  // also bad
  class Foo {
    i32 m_the_data;
  public:
    Foo();
  };
  ```

- Prefer ZII (Zero Is Initialization).

  ```c++
  template <typename T> struct Array {
  private:
    T *m_buffer = nullptr;
    i32 m_size = 0;
    i32 m_capacity = 0;
  };
  ```

  > Easy to reason with struct data if most types are initialized the same
  > way. It's also common to recieve zero initalized memory from custom
  > memory allocators.
  >
  > The name of a boolean may be flipped to support ZII. For example, instead
  > of `bool alive = true`, use `bool dead = false`.

- If any user constructor is provided, declare a default constructor.

  ```c++
  struct Shader {
    Shader() = default;
    Shader(const char *filename);

  private:
    u32 m_id = 0;
  };
  ```

- Avoid `mutable`.

  > Constant values should be constant.

- Avoid `friend`.

  > Hidden data should be hidden.

- Prefer signed integers over unsigned.

  > You can't write a for loop that walks an array backwards with unsigned
    integers:

  ```c++
  for (u32 i = some_unsigned_int; i >= 0; i--) {
    // infinite loop!
  }
  ```

- Zero initialize structs and C style arrays with `= {}`.

  ```c++
  Image m_white = {};
  Value m_stack[STACK_MAX] = {};
  ```

  > If a struct has a default constructor, `= {}` can be omited.

- Header files should use `#pragma once`.

  > Supported by commonly used compilers. Less typing compared to header
    guards.

- Use C style casts instead of `static_cast`, `dynamic_cast`, ...

  > Most type casts involve casting between integers or from `void *`. For
    these cases, C++ casts just adds extra keystrokes for little benefit.

- Set an explicit size for enums.

  > This is so that the enum type can be stored in a struct with a known
    size.

  ```c++
  enum EnemyState : i32 {
    EnemyState_Idle,
    EnemyState_Alert,
    EnemyState_Chase,
    EnemyState_Dead,
  };
  ```

- Prefer passing paremeters by pointer rather than by mutable reference.
  References are okay if its const.

  > It's easier to see that something can be changed when `&` is involved

  ```c++
  // does do_stuff get the data as a copy? const reference?
  // mutable reference? who knows?
  do_stuff(the_data);

  // oh okay, the_data is likely mutated after calling do_stuff
  do_stuff(&the_data);
  ```

- When possible, use `auto` for defining variables unless the right hand side
  is of a primative type (`int`, `size_t`, `ptrdiff_t`, `bool`, `u8 *`, etc)
  and it's not a cast from `void *` (like the result from `malloc`).

  ```c++
  struct Vec3 {
    float x, y, z;
  };

  Vec3 v1 = Vec3{3, 4, 5}; // bad
  auto v2 = Vec3{3, 4, 5}; // good
  Vec3 v3 = {3, 4, 5}; // best

  Vec3 *ptr1 = (Vec3 *)malloc(sizeof(Vec3)); // bad
  auto ptr2 = (Vec3 *)malloc(sizeof(Vec3)); // good

  auto n = strlen("hello"); // bad
  size_t n = strlen("world"); // good

  u32 n = (u32)strlen(str); // good
  auto n = (u32)strlen(str); // bad
  ```

- When include order matters, add an empty line in between the includes.

  > The rationale is that Clang-Format will reorder includes if they're
    grouped together.

  ```c++
  #include "glad2.h"
  #include <stdlib.h>
  #include <stdio.h>

  #include <GLFW/glfw3.h> // include glfw last
  ```

- Avoid `void *`.

  > There's usually some meaningful type that can be used with the pointer.
    When dealing with a raw memory block, then the type can be `u8 *`, for
    pointer arithmetic.

- Avoid operator overloading.

  > Most of the time operator overloading makes it harder to understand code.
  >
  > **Exception**: Vector and matrix types work very well with operator
  > overloading.