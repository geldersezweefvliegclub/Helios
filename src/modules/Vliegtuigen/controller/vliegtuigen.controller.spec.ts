import { Test, TestingModule } from '@nestjs/testing';
import { VliegtuigenController } from './vliegtuigen.controller';
import { VliegtuigenService } from '../service/vliegtuigen.service';

describe('VliegtuigenController', () => {
  let controller: VliegtuigenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [VliegtuigenController],
      providers: [{ provide: VliegtuigenService, useValue: jest.fn()}]
    }).compile();

    controller = module.get<VliegtuigenController>(VliegtuigenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
