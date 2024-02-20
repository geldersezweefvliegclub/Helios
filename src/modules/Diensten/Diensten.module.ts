
import { Module } from '@nestjs/common';
import { DienstenController } from './controller/Diensten.controller';
import { DienstenService } from './service/Diensten.service';
import { DienstenEntity } from './entities/Diensten.entity';
import { DienstenViewEntity } from './entities/DienstenView.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';

@Module({
    imports: [
        TypeOrmModule.forFeature([DienstenEntity, DienstenViewEntity, AuditEntity])
    ],
    controllers: [DienstenController],
    providers: [DienstenService],
})
export class DienstenModule {
}