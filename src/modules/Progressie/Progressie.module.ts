
import { Module } from '@nestjs/common';
import { ProgressieController } from './controller/Progressie.controller';
import { ProgressieService } from './service/Progressie.service';
import { ProgressieEntity } from './entities/Progressie.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';

@Module({
    imports: [
        TypeOrmModule.forFeature([ProgressieEntity, AuditEntity])
    ],
    controllers: [ProgressieController],
    providers: [ProgressieService],
})
export class ProgressieModule {
}