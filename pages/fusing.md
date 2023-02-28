[LÖVE](https://love2d.org/) is a 2D game framework. One of its features allows
you to attach a zip file to the end of the program, "fusing" all of your
game's assets and the game's executable into a single file.

You can create a fused LÖVE program on Windows like this:

```plaintext
copy /b love.exe+MyGame.zip MyGame.exe
```

Now, what the heck did we create? What kind of file is `MyGame.exe`? Sure,
with the `.exe` extension, it looks like an executable. Assuming `MyGame.zip`
contains a working LÖVE project, opening `MyGame.exe` runs the game. So yeah,
it's an honest Windows program. But how does it read the game data?

The answer: `MyGame.exe` is both a valid Windows executable and a valid zip
file! To get the game data, the program simply reads itself. To confirm that
this file is a zip file, you can open `MyGame.exe` in an archive manager,
such as 7-Zip.

![](/static/fusing/7zip.png)

Depending on the program you might have trouble reading the zip file contents.
For example, if you rename `MyGame.exe` to `MyGame.zip`, and then open it
with Windows Explorer, you'll get a complaint that the file isn't a proper
zip file.

The `copy` command that produced `MyGame.exe` isn't witchcraft. It's as
primitive as slamming `love.exe` and `MyGame.zip` next to each other. Windows
is perfectly happy to run `love.exe` with extra data added to the back, and
the extra zip file data doesn't change how the OS executes the program.

![](/static/fusing/mygame_exe.png)

Even with this arrangement, 7-Zip is perfectly happy to read the zip contents
from this file. However, Windows Explorer isn't as happy. We'll get to why
that might be the case later.

## Implementation

It's pretty easy to implement LÖVE's fusing behaviour yourself. You just need
to know a bit about how zip files are structured.

Let's start by creating a C program using [miniz](https://github.com/richgel999/miniz).
Miniz is a library that helps us read zip archives. We'll let the program read
itself as a zip file.

```c
mz_zip_archive zip = {0};
mz_bool ok = mz_zip_reader_init_file(&zip, get_executable_path(), 0);
if (!ok) {
  mz_zip_error err = mz_zip_get_last_error(&zip);
  fprintf(stderr, "failed to read zip: %s\n", mz_zip_get_error_string(err));
  exit(1);
}

// failed to read zip: invalid header or archive is corrupted
```

Okay, so like Windows Explorer, there's a complaint that our executable file
isn't a valid zip file. This is because miniz is reading the file from the
start, looking for a file entry header that isn't there.

Unlike a lot of other file formats, you start reading a zip from the back
instead of the front. The back of a zip file contains the "end of central
directory" record (or EOCD). The record contains information to locate the
central directory, which is a listing of file entries in the zip archive.
Once the central directory is located, there's enough information to get the
location where the executable stops, and where the zip file starts.

![](/static/fusing/zip_format.png)

From the diagram, you might be able to see why miniz fails to read the zip
file. It's trying to look at a file entry at the start, but instead finds the
start of the executable data. By finding the end of the executable, we can
give miniz the proper zip file data that it expects.

Assuming the zip file doesn't have any comments, the EOCD record is 22 bytes.
It contains the size of the central directory and the offset of the central
directory relative to the start of the zip archive.

![](/static/fusing/eocd.png)

> If you're wondering where these offsets and byte sizes are coming from, I
  took them off of Wikipedia's
  [Zip file format](https://en.wikipedia.org/wiki/ZIP_(file_format)) article.

We'll use this info to get the central directory. Start by getting the EOCD
record from the back of the file.

```c
// read myself
char *contents;
size_t read = read_entire_file(&contents, get_executable_path());

// then find the EOCD record
uint32_t eocd_sig = 0x06054b50; // EOCD header signature (4 bytes)
char *eocd = &contents[read - 22]; // EOCD header (22 bytes)
if (memcmp(eocd, &eocd_sig, 4) != 0) {
  fprintf(stderr, "this is not the EOCD record\n");
  exit(1);
}
```

Now we can find the location of the central directory from the back.

```c
// size of central directory (offset 12, 4 bytes)
uint32_t central_size;
memcpy(&central_size, &eocd[12], 4);

// find the central directory
uint32_t central_sig = 0x02014b50; // central directory header signature (4 bytes)
char *central_dir = eocd - central_size;
if (memcmp(central_dir, &central_sig, 4) != 0) {
  fprintf(stderr, "this is not the central directory\n");
  exit(1);
}
```

Once we've found the central directory, we can use the offset of the central
directory to jump to the start of the archive.

```c
// central directory location (offset 16, 4 bytes)
uint32_t central_offset;
memcpy(&central_offset, &eocd[16], 4);

char *zip_contents = central_dir - central_offset;
size_t zip_size = contents + read - zip_contents;
```

Pass `zip_contents` to `mz_zip_reader_init_mem` to read the zip file.

```c
mz_bool ok = mz_zip_reader_init_mem(&zip, zip_contents, zip_size, 0);
```

And that's how you can read zip data from a fused executable à la LÖVE. For
further reading, search for "self-extracting zip file" online.

