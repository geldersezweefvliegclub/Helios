import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class TypesGroepenService extends IHeliosService<TypeGroepEntity> {
  constructor(@InjectRepository(TypeGroepEntity) protected readonly repository: Repository<TypeGroepEntity>) {
    super(repository);
  }
}
