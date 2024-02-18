import { Module } from '@nestjs/common';
import { TypesGroepenController } from './controller/types-groepen.controller';
import { TypesGroepenService } from './service/types-groepen.service';
import { TypeGroepEntity } from './entities/TypeGroep.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';
import {TypeGroepViewEntity} from "./entities/TypeGroepView.entity";

@Module({
    imports: [
        TypeOrmModule.forFeature([TypeGroepEntity, AuditEntity, TypeGroepViewEntity])
    ],
    controllers: [TypesGroepenController],
    providers: [TypesGroepenService],
})
export class TypesGroepenModule {
}
