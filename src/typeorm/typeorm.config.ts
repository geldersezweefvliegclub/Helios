import {DataSource} from 'typeorm';
import {config} from 'dotenv';
import {TypeOrmConfigService} from "./typeorm.service";
import {ConfigurationUtils} from "../configuration/ConfigurationUtils";

// Load the process.env variables from the .env file
config();

/**
 * This configuration is used by the TypeORM CLI to generate migrations
 * To run the migrations, start the application. The migrations will be run automatically.
 * Uses the environment.ts file to get the database configuration (local dev config)
 */
export default new DataSource(TypeOrmConfigService.GetBaseDatasourceOptions(ConfigurationUtils.LoadConfiguration().database));
