import { Logger as TypeOrmLogger } from 'typeorm';
import { Logger } from '@nestjs/common';

export class DatabaseLogger implements TypeOrmLogger {
  private readonly logger = new Logger('DatabaseLogger');

  logQuery(query: string, parameters?: unknown[]) {
    this.logger.debug(`${query} -- Parameters: ${this.stringifyParameters(parameters)}`, {
      query: query,
      parameters: this.stringifyParameters(parameters)
    });
  }

  logQueryError(error: string, query: string, parameters?: unknown[]) {
    this.logger.error(`An error occurred during query execution: ${error}`, JSON.stringify({
      error: JSON.stringify(error),
      query: query,
      parameters: this.stringifyParameters(parameters)
    }));
  }

  logQuerySlow(time: number, query: string, parameters?: unknown[]) {
    this.logger.warn(`Time: ${time} -- Parameters: ${this.stringifyParameters(parameters)} -- ${query}`, {
      time: time,
      query: query,
      parameters: this.stringifyParameters(parameters)
    });
  }

  logMigration(message: string) {
    this.logger.log(message);
  }

  logSchemaBuild(message: string) {
    this.logger.log(message);
  }

  log(level: 'log' | 'info' | 'warn', message: string) {
    if (level === 'log') {
      return this.logger.log(message);
    }
    if (level === 'info') {
      return this.logger.debug(message);
    }
    if (level === 'warn') {
      return this.logger.warn(message);
    }
  }

  private stringifyParameters(parameters?: unknown[]) {
    try {
      return JSON.stringify(parameters);
    } catch {
      return '';
    }
  }
}
