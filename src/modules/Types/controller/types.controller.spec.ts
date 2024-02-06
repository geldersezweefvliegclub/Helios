import { Test, TestingModule } from '@nestjs/testing';
import { TypesController } from './types.controller';

describe('TypesController', () => {
  let controller: TypesController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [TypesController],
    }).compile();

    controller = module.get<TypesController>(TypesController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
