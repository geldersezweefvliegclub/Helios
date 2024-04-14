import {plainToInstance} from "class-transformer";
import {IsNumber, IsObject, IsString, validateSync} from "class-validator";
import {IDatabaseConfiguration, IHeliosConfiguration} from "./ConfigurationTypes";
import {Logger} from "@nestjs/common";

export class ConfigurationUtils {
    private readonly logger = new Logger(ConfigurationUtils.name);

    /**
     * As the configuration can be manually set by environment variables we must validate if they are correct, e.g. type and required fields.
     * @throws Error if the validation fails.
     */
    public static ValidateConfiguration = () => {
        // Create one object with all the configuration values
        const unvalidatedHeliosConfig: IHeliosConfiguration = {
            database: ConfigurationUtils.getDatabaseConfigurationFromEnvironmentVariables()
        };

        // From the plain object create class instances. This method will automatically convert the plain object to the correct types.
        const configuration = plainToInstance(HeliosConfiguration, unvalidatedHeliosConfig, {enableImplicitConversion: true});
        // Recursively validates the entire configuration object
        const configurationValidationErrors = validateSync(configuration, {skipMissingProperties: false});

        if (configurationValidationErrors.length > 0) {
            throw new Error(configurationValidationErrors.toString());
        }

        return this.LoadConfiguration();
    }

    /**
     * Get the entire Helios configuration object.
     * Should be validated by {@link ConfigurationUtils.ValidateConfiguration}, which is called when initializing the NestJS config module on application startup.
     * @constructor
     */
    public static LoadConfiguration(): IHeliosConfiguration {
        return {
            database: ConfigurationUtils.getDatabaseConfigurationFromEnvironmentVariables()
        }
    }

    /**
     * Helper method to convert environment variables to a database configuration object (unvalidated).
     * @private
     */
    private static getDatabaseConfigurationFromEnvironmentVariables(): IDatabaseConfiguration {
        return {
            host: process.env.DB_HOST,
            port: parseInt(process.env.DB_PORT),
            database: process.env.DB_DATABASE,
            username: process.env.DB_USERNAME,
            password: process.env.DB_PASSWORD,
        };
    }
}

/**
 * Class used to perform database configuration validation, not to be used directly.
 * Please use {@link ConfigurationUtils.getDatabaseConfigurationFromEnvironmentVariables} instead.
 */
class DatabaseConfiguration implements IDatabaseConfiguration {
    @IsString()
    host: string;
    @IsNumber()
    port: number;
    @IsString()
    database: string;
    @IsString()
    username: string;
    @IsString()
    password: string;
}

class HeliosConfiguration implements IHeliosConfiguration {
    @IsObject()
    database: DatabaseConfiguration;
}

