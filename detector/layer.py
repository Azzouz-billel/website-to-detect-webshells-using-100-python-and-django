import numpy as np
from base_layer import Layer

class Dense(Layer):
    def __init__(self, input_size, output_size):
        # He initialization: scale weights by sqrt(2 / fan_in)
        # Prevents vanishing gradients in deep ReLU networks
        he_scale = np.sqrt(2.0 / input_size)
        self.weights = np.random.randn(output_size, input_size) * he_scale

        # Zero-initialize biases for ReLU compatibility
        self.bias = np.zeros((output_size, 1))

        # Momentum velocity terms (SGD with momentum)
        self.v_w = np.zeros_like(self.weights)
        self.v_b = np.zeros_like(self.bias)

    def forward(self, input):
        self.input = input
        return np.dot(self.input, self.weights.T) + self.bias.T

    def backward(self, output_gradient, learning_rate, beta):
        weights_gradient = np.dot(output_gradient.T, self.input)
        input_gradient   = np.dot(output_gradient, self.weights)
        bias_gradient    = np.sum(output_gradient, axis=0, keepdims=True).T

        # Momentum update
        self.v_w = beta * self.v_w + (1 - beta) * weights_gradient
        self.v_b = beta * self.v_b + (1 - beta) * bias_gradient

        self.weights -= learning_rate * self.v_w
        self.bias    -= learning_rate * self.v_b

        return input_gradient
