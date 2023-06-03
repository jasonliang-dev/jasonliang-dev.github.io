C++ is a ridiculously complex language. Most people stick to a subset of its
features. I've been using a lot of C++ for side projects that never see the
light of day, and over time, I've developed a style that I like and I wanted
to document it.

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

- Prefer explicitly sized integers such as `int32_t`. Use the following type
  aliases:

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

- Never use `throw`. Never catch exceptions.

  > Read [Exceptions — And Why Odin Will Never Have Them](https://www.gingerbill.org/article/2018/09/05/exceptions-and-why-odin-will-never-have-them/).
  > **TL;DR**: Errors are not special. Treat errors as values.

- Custom memory allocator comes first, then output parameters, then the rest
  of the function parameters.

  ```c++
  bool make_texture(Allocator *a, Texture *out, u8 *data, i32 w, i32 h);
  ```

- If object creation can fail, use functions instead of constructors.

  ```c++
  // bad
  // if this can fail. how do you let the user know?
  // is there a global error flag? or does it throw an exception?
  // remember that we're trying to avoid exceptions
  Bullet::Bullet(Entity *owner);

  // good
  Maybe<Bullet> make_bullet(Entity *owner);

  // also good
  bool make_bullet(Bullet *out, Entity *owner);
  ```

- Avoid writing destructors. No RAII.

  > [Defer](http://www.gingerbill.org/article/2015/08/19/defer-in-cpp/)
  > can help with some of the friction that comes with explicit destruction.

- Prefer string views over C-style strings or `std::string`.

  > Unlike C strings, string views stores the length. Unlike string buffers
  > (like `std::string`), substring is constant time and does not require
  > memory allocation. Also, copying a string view is very cheap.

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

  for (i32 i = some_unsigned_int; i >= 0; i--) {
    // signed/unsigned mismatch
  }
  ```

- Zero initialize structs and C style arrays with `= {}`.

  ```c++
  Image m_white = {};
  Value m_stack[STACK_MAX] = {};
  ```

- Header files should use `#pragma once`.

  > Supported by commonly used compilers. Less typing compared to header
    guards.

- Prefer C style casts instead of `static_cast`, `dynamic_cast`, ...

  > Most type casts involve casting between integers or from `void *`. For
    these cases, C++ casts just adds extra keystrokes for little benefit.

- Use `typename` for declaring template types.

  ```c++
  template <class T> struct Array; // bad
  template <typename T> struct Array; // good
  ```

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

  > It's easier to see that something can be changed when `&` is involved at
    the call site.

  ```c++
  // does do_stuff get the data as a copy? const reference?
  // mutable reference? who knows?
  do_stuff(the_data);

  // oh okay, the_data is likely mutated after calling do_stuff
  do_stuff(&the_data);
  ```

- When include order matters, add an empty line in between the includes.

  > The rationale is that Clang-Format will reorder includes if they're
    grouped together.

  ```c++
  #include "texture.h"

  #define STB_IMAGE_IMPLEMENTATION
  #include "stb_image.h"
  ```

- Avoid `void *`.

  > There's usually some meaningful type that can be used with the pointer.
    When dealing with a raw memory block, then the type can be `u8 *`, for
    pointer arithmetic.

- The condition in an if statement should be a boolean type. No truthy/falsy
  expressions.

  ```c++
  char *buf = get_buf_data();
  if (buf) {} // bad
  if (buf != nullptr) {} // good
  ```

- Use raw string literals for multi-line strings.

  ```c++
  auto fragment = R"(
    #version 330 core

    in vec2 v_texindex;
    out vec4 f_color;
    uniform sampler2D u_texture;

    void main() {
      f_color = texture(u_texture, v_texindex);
    }
  )";
  ```