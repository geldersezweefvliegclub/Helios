import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { VliegtuigenEntity } from './entities/Vliegtuigen.entity';
import { VliegtuigenController } from './controller/vliegtuigen.controller';
import { VliegtuigenService } from './service/vliegtuigen.service';

@Module({
    imports: [
        TypeOrmModule.forFeature([VliegtuigenEntity])
    ],
    controllers: [VliegtuigenController],
    providers: [VliegtuigenService],
})
export class VliegtuigenModule {
}
