import {Module} from '@nestjs/common';
import {AppController} from './app.controller';
import {AppService} from './app.service';
import {ConfigModule} from '@nestjs/config';
import {TypeOrmModule} from "@nestjs/typeorm";
import {TypeOrmConfigService} from "./typeorm/typeorm.service";
import {TypesModule} from "./modules/Types/types.module";

@Module({
    imports: [
        ConfigModule.forRoot({isGlobal: true}),
        TypeOrmModule.forRootAsync({useClass: TypeOrmConfigService}),
        TypesModule
    ],
    controllers: [AppController],
    providers: [AppService, TypeOrmConfigService],
})
export class AppModule {
}
