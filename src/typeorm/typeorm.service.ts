import {Injectable, Logger} from "@nestjs/common";
import { ConfigService } from '@nestjs/config';
import { TypeOrmModuleOptions, TypeOrmOptionsFactory } from '@nestjs/typeorm';
import {DatabaseLogger} from "./DatabaseLogger";


@Injectable()
export class TypeOrmConfigService implements TypeOrmOptionsFactory {
    private readonly logger = new Logger(TypeOrmConfigService.name);
    private readonly databaseLogger = new DatabaseLogger();
    constructor(private config: ConfigService) {}


    /**
     * Builds the options to use for TypeORM
     */
    public createTypeOrmOptions(): TypeOrmModuleOptions {
        this.logger.log("Configuring database connection with TypeORM");
        const databaseName = this.config.get<string>('DB_DATABASE');
        const databaseHost = this.config.get<string>('DB_HOST');
        const databasePort = this.config.get<string>('DB_PORT');
        const databaseUsername = this.config.get<string>('DB_USERNAME');
        const databasePassword = this.config.get<string>('DB_PASSWORD');

        if (!databaseName || !databaseHost || !databasePort || !databaseUsername || !databasePassword) {
            throw new Error("Missing required properties for database connection!")
        }

        return {
            type: 'mariadb',
            host: databaseHost,
            port: Number(databasePort),
            database: databaseName,
            username: databaseUsername,
            password: databasePassword,
            //   migrations: ['dist/migrations/*.{ts,js}'],
            entities: [__dirname + '/../**/*.entity.ts'],
            logger: this.databaseLogger,
            synchronize: false, //todo find out what it is, do not set to TRUE in production mode - possible data loss
            autoLoadEntities: true,
            logging: true
        };
    }
}
