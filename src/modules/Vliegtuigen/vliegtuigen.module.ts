import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { VliegtuigenEntity } from './entities/Vliegtuigen.entity';
import { VliegtuigenController } from './controller/vliegtuigen.controller';
import { VliegtuigenService } from './service/vliegtuigen.service';
import { AuditEntity } from '../../core/entities/Audit.entity';

@Module({
    imports: [
        TypeOrmModule.forFeature([VliegtuigenEntity, AuditEntity])
    ],
    controllers: [VliegtuigenController],
    providers: [VliegtuigenService],
})
export class VliegtuigenModule {
}
