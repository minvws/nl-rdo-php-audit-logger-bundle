# Audit Logger for Symfony (nl-rdo-php-audit-logger-bundle)

This package extends the [minvws/audit-logger](https://github.com/minvws/nl-rdo-php-audit-logger) package and provides a 
generic logging service for Symfony applications for the RDO platform. It allows to easily log events to the database, 
syslog or other destinations.

## Prerequisites

- PHP >= 8.1
- Composer
- Symfony >= 6.3

## Installation

### Composer

Install the package through composer:

```bash
$ composer require minvws/audit-logger-bundle
```

### Configuration

The package can be configured in the `audit_logger.yaml` file. There is a generic configuration for encryption:
    
```yaml
public_key:   # The public key to use for encryption
private_key:  # The private key to use for encryption
```

There are currently four logging destinations available: psr, database, file and rabbitmq:

#### PSR-3 Logger (psr_logger)

Generic PSR-3 logger that can be used to log to any PSR-3 compatible logger.

Possible configuration options:

| Option    | Value type | Default | Description                                 |
|-----------|------------|---------|---------------------------------------------|
| enabled   | boolean    | true    | Enable logging                              |
| encrypted | boolean    | true    | Encrypt the log data                        |
| log_pii   | boolean    | true    | Log Personal Identifiable Information (PII) |

#### Database Logger (doctrine_logger)

Log data to the database.

Possible configuration options:

| Option    | Value type | Default | Description                                 |
|-----------|------------|---------|---------------------------------------------|
| enabled   | boolean    | true    | Enable logging                              |
| encrypted | boolean    | true    | Encrypt the log data                        |
| log_pii   | boolean    | true    | Log Personal Identifiable Information (PII) |

#### File Logger (file_logger)

Log data to the database.

Possible configuration options:

| Option    | Value type | Default                       | Description                                 |
|-----------|------------|-------------------------------|---------------------------------------------|
| enabled   | boolean    | true                          | Enable logging                              |
| path      | string     | '%kernel.logs_dir%/audit.log' | Path to where log file is written           |
| encrypted | boolean    | true                          | Encrypt the log data                        |
| log_pii   | boolean    | true                          | Log Personal Identifiable Information (PII) |

#### RabbitMQ Logger (rabbitmg_logger)

Log data to RabbitMQ.

Possible configuration options:

| Option            | Value type | Default     | Description                                 |
|-------------------|------------|-------------|---------------------------------------------|
| enabled           | boolean    | true        | Enable logging                              |
| additional_events | array      | []          | Register additional event to publish        |
| routing_key       | string     | ''          | Optional routing key                        |
| log_pii           | boolean    | true        | Log Personal Identifiable Information (PII) |

## More information

See [minvws/audit-logger](https://github.com/minvws/nl-rdo-php-audit-logger) for more information.

## Creating custom events

Creating a custom event is easy. You can create a new class that extends the `MinVWS\AuditLogger\Events\Logging\GeneralLogEvent` class.

```php
  use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
  
  class MyCustomEvent extends GeneralLogEvent
  {
      public const EVENT_CODE = '991414';
      public const EVENT_KEY = 'my_custom_event';
  }
```

## Contributing
If you encounter any issues or have suggestions for improvements, please feel free to open an issue or submit a pull request on the GitHub repository of this package.

## License
This package is open-source and released under the European Union Public License version 1.2. You are free to use, modify and distribute the package in accordance with the terms of the license.

## Part of iCore
This package is part of the iCore project.
