
import { Module } from '@nestjs/common';
import { RoosterController } from './controller/Rooster.controller';
import { RoosterService } from './service/Rooster.service';
import { RoosterEntity } from './entities/Rooster.entity';
import { RoosterViewEntity } from './entities/RoosterView.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';

@Module({
    imports: [
        TypeOrmModule.forFeature([RoosterEntity, RoosterViewEntity, AuditEntity])
    ],
    controllers: [RoosterController],
    providers: [RoosterService],
})
export class RoosterModule {
}