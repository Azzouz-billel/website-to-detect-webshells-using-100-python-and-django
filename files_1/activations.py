import numpy as np
from base_layer import Layer

class Activation(Layer):
    def __init__(self, activation, activation_prime):
        self.activation = activation
        self.activation_prime = activation_prime

    def forward(self, input):
        self.input = input
        return self.activation(self.input)

    def backward(self, output_gradient, learning_rate, beta):
        return np.multiply(output_gradient, self.activation_prime(self.input))

class Tanh(Activation):
    def __init__(self):
        def tanh(x):
            return np.tanh(x)

        def tanh_prime(x):
            return 1 - np.tanh(x) ** 2

        super().__init__(tanh, tanh_prime)

class Relu(Activation):
    def __init__(self):
        relu       = lambda x: np.where(np.asarray(x) > 0, x, 0)
        relu_prime = lambda x: np.where(np.asarray(x) > 0, 1, 0)
        super().__init__(relu, relu_prime)

class Sigmoid(Activation):
    def __init__(self):
        def sigmoid(x):
            clipped_x = np.clip(x, -500, 500)
            return 1 / (1 + np.exp(-clipped_x))

        def sigmoid_prime(x):
            clipped_x = np.clip(x, -500, 500)
            s = 1 / (1 + np.exp(-clipped_x))
            return s * (1 - s)

        super().__init__(sigmoid, sigmoid_prime)
