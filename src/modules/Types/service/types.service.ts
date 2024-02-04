import { Injectable } from '@nestjs/common';
import { FindManyOptions, Repository } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';
import { InjectRepository } from '@nestjs/typeorm';

@Injectable()
export class TypesService {
  constructor(@InjectRepository(TypeEntity) private readonly typesRepository: Repository<TypeEntity>) {
  }

  async getObject(id: number) {
    return this.typesRepository.findOne({ where: { ID: id } });
  }

  async getObjects(filter: FilterCriteria): Promise<TypeEntity[]> {
    const findOptions: FindManyOptions<TypeEntity> = {
      where: filter
    };
    return this.typesRepository.find(findOptions);
  }
}
