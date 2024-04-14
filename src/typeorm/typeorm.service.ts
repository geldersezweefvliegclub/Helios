import {Injectable, Logger} from '@nestjs/common';
import {ConfigService} from '@nestjs/config';
import {TypeOrmModuleOptions, TypeOrmOptionsFactory} from '@nestjs/typeorm';
import {DatabaseLogger} from './DatabaseLogger';
import {join} from 'path';
import {DataSourceOptions} from "typeorm";
import {IDatabaseConfiguration, IHeliosConfiguration} from "../configuration/ConfigurationTypes";


@Injectable()
export class TypeOrmConfigService implements TypeOrmOptionsFactory {
    private static readonly logger = new Logger(TypeOrmConfigService.name);
    private static readonly databaseLogger = new DatabaseLogger();

    constructor(private config: ConfigService) {
    }


    /**
     * Builds the options to use for TypeORM
     */
    public createTypeOrmOptions(): TypeOrmModuleOptions {
        const dbConfig = this.config.get<IHeliosConfiguration['database']>('database');

        return {
            ...TypeOrmConfigService.GetBaseDatasourceOptions(dbConfig),
            migrationsRun: false,
            synchronize: false, // Please see comments in GetBaseDatasourceOptions method
            autoLoadEntities: true,
        };
    }

    /**
     * Base datasource options used for application AND generating / running migrations.
     * For application-specific options, see {@link TypeOrmConfigService.createTypeOrmOptions}
     */
    public static GetBaseDatasourceOptions(dbConfig: IDatabaseConfiguration): DataSourceOptions {
        // Make sure we don't log our password but we do log the rest of the configuration
        const configSafeToLog = {...dbConfig, password: 'REDACTED'};
        this.logger.log(`Connecting to database user the following configuration: ${JSON.stringify(configSafeToLog, null, 2)}`);

        return {
            type: 'mariadb',
            host: dbConfig.host,
            port: dbConfig.port,
            database: dbConfig.database,
            username: dbConfig.username,
            password: dbConfig.password,
            migrations: [join(__dirname, '../../../migrations/*.{ts,js}')],
            entities: [join(__dirname, '../../**/*.entity.{ts,js}')],
            logger: this.databaseLogger,
            logging: true,
            // Do NOT set to true in production mode, high chance of data loss
            // It automatically syncs your database with the models in the source code without creating migrations
            // Even for local development, it is recommended to create migrations and run them manually
            // When your development work is done, and you have many migrations you want to merge into one bigger migration:
            // 1. Check if other database already have these migrations applied. If so I would not recommend merging them.
            // 2. If you are sure you want to merge migrations, delete the migration .ts files
            // 3. Drop your database and run the migrations that are currently in GIT (you will have most up-to-date database)
            // 4. Generate one big migration using TypeORM CLI
            synchronize: false
        }
    }
}
