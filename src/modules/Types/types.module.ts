import { Module } from '@nestjs/common';
import { TypesController } from './controller/types.controller';
import { TypesService } from './service/types.service';
import { TypeEntity } from './entities/Type.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';
import {TypeViewEntity} from "./entities/TypeView.entity";

@Module({
    imports: [
        TypeOrmModule.forFeature([TypeEntity, AuditEntity, TypeViewEntity]),
    ],
    controllers: [TypesController],
    providers: [TypesService],
})
export class TypesModule {
}
