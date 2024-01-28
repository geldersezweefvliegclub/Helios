import {Injectable, Logger} from "@nestjs/common";
import { ConfigService } from '@nestjs/config';
import { TypeOrmModuleOptions, TypeOrmOptionsFactory } from '@nestjs/typeorm';


@Injectable()
export class TypeOrmConfigService implements TypeOrmOptionsFactory {
    private readonly logger = new Logger(TypeOrmConfigService.name);
    constructor(private config: ConfigService) {}


    /**
     * Builds the options to use for TypeORM
     */
    public createTypeOrmOptions(): TypeOrmModuleOptions {
        const dbConfig = this.config.get<IEnvironmentConfiguration['database']>('database');
        const productionMode = this.config.get<IEnvironmentConfiguration['production']>('production');

        const configSafeToLog = {...dbConfig, password: 'REDACTED'};
        this.logger.log(`Connecting to database user the following configuration: ${JSON.stringify(configSafeToLog, null, 2)}`);
        return {
            type: 'mongodb',
            host: dbConfig.host,
            port: dbConfig.port,
            database: dbConfig.database,
            username: dbConfig.username,
            password: dbConfig.password,
            authSource: 'admin',
            //   migrations: ['dist/migrations/*.{ts,js}'],
            logger: 'advanced-console',
            synchronize: !productionMode, // do not set to TRUE in production mode - possible data loss
            autoLoadEntities: true,
            logging: true
        };
    }
}
