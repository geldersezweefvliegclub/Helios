import {Injectable} from "@nestjs/common";
import * as fs from "fs/promises";

@Injectable()
export class ConfigurationService {
    constructor(){
        this.loadConfiguration();
    }

    private async loadConfiguration() {
        const configurationFile = await fs.readFile('../assets/appsettings.json');

        console.log(configurationFile);
    }
}