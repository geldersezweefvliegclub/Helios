
import { Module } from '@nestjs/common';
import { LedenController } from './controller/Leden.controller';
import { LedenService } from './service/Leden.service';
import { LedenEntity } from './entities/Leden.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';
import {LedenViewEntity} from "./entities/LedenView.entity";

@Module({
    imports: [
        TypeOrmModule.forFeature([LedenEntity, AuditEntity, LedenViewEntity])
    ],
    controllers: [LedenController],
    providers: [LedenService],
})
export class LedenModule {
}
