# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning] (http://semver.org/).
For change log format, use [Keep a Changelog] (http://keepachangelog.com/).

## [1.2.1] - 2020-09-23
### Removed
- Remove Smtp::__set_state() method ; incompatible signature with PHP 8

## [1.2] - 2020-04-14
### Changed
- Exceptions more verbose from SMTP transport
- PSR-12 refactoring
- Change `\Berlioz\Mailer\Mail` class as deprecated, replaced by `\Berlioz\Mailer\PhpMail`

## [1.1.2] - 2020-02-10
### Added
- Add coverage options in phpunit.xml.dist

### Changed
- Fix deprecated (PHP7.4) array and string offset access syntax with curly braces

### Removed
- Remove composer.lock

## [1.1.1] - 2019-11-14
### Changed
- Fix null values with mb_* functions
- Fix lower case modification of excluded headers

## [1.1.0] - 2019-11-14
### Changed
- Fix deprecated (PHP7.4) array and string offset access syntax with curly braces
- Truncate length of base64 fixed to 76 (for best SpamAssassin score)
- Default length of boundaries fixed to 15 (for best SpamAssassin score)
- Enabled strict types

## [1.0.0] - 2018-06-29
First version