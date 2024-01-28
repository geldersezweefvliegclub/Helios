import {Module} from '@nestjs/common';
import {TypesController} from './controller/types.controller';
import {TypesService} from './service/types.service';
import {Type} from "./models/Type";
import {TypeOrmModule} from "@nestjs/typeorm";

@Module({
    imports: [
        TypeOrmModule.forFeature([Type])
    ],
    controllers: [TypesController],
    providers: [TypesService],
})
export class TypesModule {
}
