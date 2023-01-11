Here is one way to implement the classic object-oriented bank account in Lua:

```lua
local Account = {}
Account.__index = Account

function Account.new(name, balance)
  local self = {}
  self.name = name
  self.balance = balance
  return setmetatable(self, Account)
end

function Account:withdraw(n)
  self.balance = self.balance - n
end

function Account:deposit(n)
  self.balance = self.balance + n
end

local acc = Account.new("Jason", 1000)
acc:withdraw(200)
print(acc.balance) -- prints 800
```

But if you have some data that's heavily tied to code written in C (or any
language, as long as it's part of the host program), then you might want to
implement the same pattern outside of Lua. This article describes how you can
do that.

## Calling C functions from Lua

Just to get started off, we'll expose a C function that we can call on the Lua
side. Let's start off with a host program:

```c
#define MAKE_LIB
#include "lua/onelua.c"

int main(void) {
  lua_State *L = luaL_newstate();
  luaL_openlibs(L);

  luaL_dostring(L, "print 'hello from lua'");

  lua_close(L);
}
```

The `lua` directory, which contains `onelua.c` comes from
[Lua's GitHub repo](https://github.com/lua/lua).

Compile and run just to check everything's working and the `hello from lua`
string is being printed. Now create a `main.lua` file to run from:

```lua
print 'I am being printed from main.lua'
```

Back in the C program, you could use `luaL_dofile`, but I'll continue to use
`luaL_dostring` because it's easy to inspect errors with the follow snippet:

```c
const char *run = "xpcall(function ()\n"
                  "  require 'main'\n"
                  "end, function(err)\n"
                  "  print(tostring(err))\n"
                  "  print(debug.traceback(nil, 2))\n"
                  "  os.exit(1)\n"
                  "end)\n";
luaL_dostring(L, run);
```

So if there's any syntax or runtime errors raised by `main.lua`, we print the
error and the backtrace. The same can be done with just the C API, but it
takes a bit more work.

We'll create a C function that only prints the string "hi". All C functions
have the same signature. It takes a `lua_State *`, and returns an
integer representing the number of return values:

```c
int sys_say_hi(lua_State *L) {
  printf("hi\n");
  return 0;
}
```

We're returning 0, because there's no values to return after printing "hi".

The function starts with `sys_` for namespacing. We'll use this function from
`main.lua` like so:

```lua
sys.say_hi()
```

This can be done by creating a table with `luaL_newlib`, which accepts a null
terminated array of C functions to insert into a table that we'll call
`sys`:

```c
int open_sys(lua_State *L) {
  luaL_Reg reg[] = {
    {"say_hi", sys_say_hi},
    {NULL, NULL},
  };

  luaL_newlib(L, reg);

  return 1;
}

int main(void) {
  // ...
  luaL_requiref(L, "sys", open_sys, 1); // add before luaL_dostring
  // ...
}
```

Now with all of this code in place, check if the program actually prints
out "hi".

## Yeah I'd like to open a bank account

We need an account type to play with:

```c
typedef struct {
  char *name;
  int balance;
} Account;
```

From Lua, making a new account will look like this:

```lua
local acc = sys.Account.new("Jason", 1000)
```

The `new` function will return a full userdata value with a metatable
attached. Userdata is simply a chunk of data for our Account type.

To demostrate how an account interacts with the garbage collector, the name
of the account will be created in heap memory using `malloc`.

```c
int mt_account_new(lua_State *L) {
  const char *name = luaL_checkstring(L, 1);
  int balance = (int)luaL_checkinteger(L, 2);

  Account *self = lua_newuserdata(L, sizeof(Account));

  self->name = malloc(strlen(name) + 1);
  strcpy(self->name, name);

  self->balance = balance;

  luaL_setmetatable(L, "mt_account");

  return 1;
}
```

This roughly translates to the following Lua code, but the difference is that
the constructor returns a table instead of full userdata:

```lua
function mt_account.new(name, balance)
  local self = {}
  self.name = name
  self.balance = balance
  return setmetatable(self, mt_account)
end
```

The "mt_account" will be a table in the Lua registry. The Lua registry is a
place to store values for the host program to use. It can be accessed in Lua
with `debug.getregistry()`. So this:

```lua
local acc = sys.Account.new("Jason", 1000)
```

Is the same as this:

```lua
local reg = debug.getregistry()
local acc = reg.mt_account.new("Jason", 1000)
```

To create the `mt_account` metatable, we can use `luaL_newmetatable`.

```c
int push_mt_account(lua_State *L) {
  luaL_Reg reg[] = {
      {"new", mt_account_new},
      {NULL, NULL},
  };

  luaL_newmetatable(L, "mt_account");
  luaL_setfuncs(L, reg, 0);
  lua_pushvalue(L, -1);
  lua_setfield(L, -2, "__index");

  return 1;
}
```

This function will push a new metatable on top of the Lua stack.
`luaL_setfuncs` is similar to `luaL_newlib`, it'll attach the `new` method to
the `mt_account` table. We'll be adding more methods later.

`lua_setfield(L, -2, "__index")` is there to set the `__index` value of our
metatable to itself. `lua_setfield` pops the value at the top of the stack,
but we're going to need to keep it so we can access it under the `sys` table
that we had created eariler. That's why `lua_pushvalue(L, -1)` is there.

The equivalent Lua code looks like this:

```lua
local mt_account = {}
mt_account.new = function() { --[[ mt_account_new c function ]] }
mt_account.__index = mt_account
return mt_account
```

And here's a visual of the Lua stack before calling `luaL_newmetatable`:

| index  | value |
|---|-----|
| 2 | ... |
| 1 | ... |

After calling `luaL_newmetatable` and `luaL_setfuncs`:

| index  | value |
|---|-----|
| 3 | mt_account |
| 2 | ... |
| 1 | ... |

Then after `lua_pushvalue`:

| index  | value |
|---|-----|
| 4 | mt_account |
| 3 | mt_account |
| 2 | ... |
| 1 | ... |

Finally, after `lua_setfield`:

| index  | value |
|---|-----|
| 3 | mt_account |
| 2 | ... |
| 1 | ... |

Our `push_mt_account` function returns one value, the metatable. We'll add it
to the `sys` table:

```c
int open_sys(lua_State *L) {
  // ...

  luaL_newlib(L, reg);
  register_mt_account(L);
  lua_setfield(L, -2, "Account");

  return 1;
}
```

## Adding more methods

Withdrawing money will look like this:

```lua
acc:withdraw(200)
```

Which is syntax sugar for:

```lua
acc.withdraw(acc, 200)
```

So we need a function that takes an account for the first parameter, and a
number for the second. Here it is in C:

```c
int mt_account_withdraw(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  int n = (int)luaL_checknumber(L, 2);
  self->balance -= n;
  return 0;
}
```

You can probably guess how the deposit function will look like.

We don't have a way to inspect the account. So we'll create getters for that.

```c
int mt_account_get_name(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  lua_pushstring(L, self->name);
  return 1;
}

int mt_account_get_balance(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  lua_pushinteger(L, self->balance);
  return 1;
}
```

Add the newly created functions to the function list:

```c
luaL_Reg reg[] = {
  {"new", mt_account_new},
  {"withdraw", mt_account_withdraw},
  {"get_name", mt_account_get_name},
  {"get_balance", mt_account_get_balance},
  {NULL, NULL},
};
```

## Cleaning up the mess

Our program is nearly done, but there's just one more thing.

Whenever we're done with an account, we leak memory. This is because we used
`malloc` for the account's name and never called `free`. The `__gc`
metamethod let's us perform some stuff right before we lose the account to
the garbage collector. Add the following to the function list:

```c
luaL_Reg reg[] = {
  // ...
  {"__gc", mt_account_delete},
  // ...
};
```

And now here's the place where we free the memory allocated for an account's
name:

```c
int mt_account_delete(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  free(self->name);
}
```

We're done! Test it out in Lua to check that everything works

```lua
local acc = sys.Account.new("Jason", 1000)
acc:withdraw(200)
print(string.format("name: %s, balance: %d", acc:get_name(), acc:get_balance()))
acc:deposit(100)
print(string.format("name: %s, balance: %d", acc:get_name(), acc:get_balance()))

--[[
name: Jason, balance: 800
name: Jason, balance: 900
]]
```

## Full source code

```c
#define MAKE_LIB
#include "lua/onelua.c"

typedef struct {
  char *name;
  int balance;
} Account;

int mt_account_new(lua_State *L) {
  const char *name = luaL_checkstring(L, 1);
  int balance = (int)luaL_checkinteger(L, 2);

  Account *self = lua_newuserdata(L, sizeof(Account));

  self->name = malloc(strlen(name) + 1);
  strcpy(self->name, name);

  self->balance = balance;

  luaL_setmetatable(L, "mt_account");

  return 1;
}

int mt_account_delete(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  free(self->name);
  return 0;
}

int mt_account_deposit(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  int n = (int)luaL_checknumber(L, 2);
  self->balance += n;
  return 0;
}

int mt_account_withdraw(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  int n = (int)luaL_checknumber(L, 2);
  self->balance -= n;
  return 0;
}

int mt_account_get_name(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  lua_pushstring(L, self->name);
  return 1;
}

int mt_account_get_balance(lua_State *L) {
  Account *self = luaL_checkudata(L, 1, "mt_account");
  lua_pushinteger(L, self->balance);
  return 1;
}

int register_mt_account(lua_State *L) {
  luaL_Reg reg[] = {
    {"new", mt_account_new},
    {"__gc", mt_account_delete},
    {"deposit", mt_account_deposit},
    {"withdraw", mt_account_withdraw},
    {"get_name", mt_account_get_name},
    {"get_balance", mt_account_get_balance},
    {0, 0},
  };

  luaL_newmetatable(L, "mt_account");
  luaL_setfuncs(L, reg, 0);
  lua_pushvalue(L, -1);
  lua_setfield(L, -2, "__index");

  return 1;
}

int sys_say_hi(lua_State *L) {
  printf("hi\n");
  return 0;
}

int open_sys(lua_State *L) {
  luaL_Reg reg[] = {
    {"say_hi", sys_say_hi},
    {0, 0},
  };

  luaL_newlib(L, reg);
  register_mt_account(L);
  lua_setfield(L, -2, "Account");

  return 1;
}

int main(void) {
  lua_State *L = luaL_newstate();

  luaL_openlibs(L);
  luaL_requiref(L, "sys", open_sys, 1);

  const char *run = "xpcall(function ()\n"
                    "  require 'main'\n"
                    "end, function(err)\n"
                    "  print(tostring(err))\n"
                    "  print(debug.traceback(nil, 2))\n"
                    "  os.exit(1)\n"
                    "end)\n";
  luaL_dostring(L, run);

  lua_close(L);
}
```
