import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';

@Injectable()
export class VliegtuigenService extends IHeliosService<VliegtuigenEntity> {
  constructor(@InjectRepository(VliegtuigenEntity) protected readonly repository: Repository<VliegtuigenEntity>) {
    super(repository);
  }
}
