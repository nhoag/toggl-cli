# toggl-cli

CLI interface to Toggl API

## Installation

```
git clone git@github.com:nhoag/toggl-cli.git
cd toggl-cli
composer install
```

## Configuration

```
cp .toggl-config.dist .toggl-config
vi .toggl-config # Add your Toggl token
```

## Usage

```
./bin/console
Toggl CLI version VERSION

Usage:
 [options] command [arguments]

Options:
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question

Available commands:
 help             Displays help for a command
 list             Lists commands
add
 add:entry        Add Toggl Entry
get
 get:projects     Get Toggl Projects
 get:tags         Get Toggl Tags
 get:workspaces   Get Toggl Workspaces
```
