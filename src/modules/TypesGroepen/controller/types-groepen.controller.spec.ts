import { Test, TestingModule } from '@nestjs/testing';
import { TypesGroepenController } from './types-groepen.controller';
import { TypesGroepenService } from '../service/types-groepen.service';

describe('TypesGroepenController', () => {
  let controller: TypesGroepenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [TypesGroepenController],
      providers: [
        {
          provide: TypesGroepenService,
          useValue: jest.fn()
        }
      ]
    }).compile();

    controller = module.get<TypesGroepenController>(TypesGroepenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
