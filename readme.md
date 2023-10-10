# jasonliang-dev.github.io

## Development

Install Go and SASS, then:

```sh
go run . serve
```

## Deployment

```sh
go run . build # creates a `dist` directory
gh-pages -d dist # deploy to GitHub
```