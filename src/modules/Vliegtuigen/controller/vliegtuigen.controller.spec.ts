import { Test, TestingModule } from '@nestjs/testing';
import { VliegtuigenController } from './vliegtuigen.controller';

describe('VliegtuigenController', () => {
  let controller: VliegtuigenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [VliegtuigenController],
    }).compile();

    controller = module.get<VliegtuigenController>(VliegtuigenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
