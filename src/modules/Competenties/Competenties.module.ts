
import { Module } from '@nestjs/common';
import { CompetentiesController } from './controller/Competenties.controller';
import { CompetentiesService } from './service/Competenties.service';
import { CompetentiesEntity } from './entities/Competenties.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';
import {CompetentiesViewEntity} from "./entities/CompetentiesView.entity";

@Module({
    imports: [
        TypeOrmModule.forFeature([CompetentiesEntity, AuditEntity, CompetentiesViewEntity])
    ],
    controllers: [CompetentiesController],
    providers: [CompetentiesService],
})
export class CompetentiesModule {
}